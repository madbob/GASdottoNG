<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use DB;
use Auth;
use Theme;

use App\Supplier;
use App\Product;
use App\Order;
use App\Aggregate;
use App\Booking;
use App\BookedProduct;

/*
	Attenzione, quando si maneggia in questo file bisogna ricordare la
	distinzione tra Ordine e Aggregato.
	Un Ordine fa riferimento ad un fornitore (per il quale l'utente può
	avere o no permessi di modifica) e contiene dei prodotti. Un Aggregato è
	un insieme di Ordini.
	Per comodità, qui si assume che tutti gli Ordini siano sempre parte di
	un Aggregato, anche se contiene solo l'Ordine stesso. In alcuni casi gli
	ID passati come parametro alle funzioni fanno riferimento ad un Ordine,
	in altri casi ad un Aggregato.
*/

class OrdersController extends Controller
{
	public function __construct()
	{
		$this->middleware('auth');
	}

	private function defaultOrders()
	{
		/*
			La selezione degli ordini da visualizzare si può forse
			fare con una query complessa, premesso che bisogna
			prendere in considerazione i permessi che l'utente
			corrente ha nei confronti dei fornitori degli ordini
			inclusi negli aggregati
		*/

		$orders = [];

		$aggregates = Aggregate::with('orders')->get();
		foreach($aggregates as $aggregate) {
			$ok = false;

			foreach($aggregate->orders as $order) {
				if ($order->status == 'open') {
					$ok = true;
					break;
				}
				if ($order->supplier->userCan('supplier.orders|supplier.shippings')) {
					$ok = true;
					break;
				}
			}

			if ($ok == true)
				$orders[] = $aggregate;
		}

		return $orders;
	}

	public function index()
	{
		$orders = $this->defaultOrders();
		return Theme::view('pages.orders', ['orders' => $orders]);
	}

	public function store(Request $request)
	{
		DB::beginTransaction();

		$supplier = Supplier::findOrFail($request->input('supplier_id', -1));
		if ($supplier->userCan('supplier.orders') == false)
			return $this->errorResponse('Non autorizzato');

		$o = new Order();
		$o->supplier_id = $request->input('supplier_id');

		$now = date('Y-m-d');
		$o->start = $this->decodeDate($request->input('start'));
		$o->end = $this->decodeDate($request->input('end'));
		$o->status = $request->input('status');

		$s = $request->input('shipping');
		if ($s != '')
			$o->shipping = $this->decodeDate($s);
		else
			$o->shipping = '';

		$a = new Aggregate();
		$a->save();

		$o->aggregate_id = $a->id;
		$o->save();

		$o->products()->sync($supplier->products);

		return $this->successResponse([
			'id' => $a->id,
			'header' => $a->printableHeader(),
			'url' => url('orders/' . $a->id)
		]);
	}

	public function show($id)
	{
		$a = Aggregate::findOrFail($id);
		return Theme::view('order.aggregate', ['aggregate' => $a]);
	}

	public function update(Request $request, $id)
	{
		DB::beginTransaction();

		$order = Order::findOrFail($id);
		if ($order->supplier->userCan('supplier.orders') == false)
			return $this->errorResponse('Non autorizzato');

		$order->start = $this->decodeDate($request->input('start'));
		$order->end = $this->decodeDate($request->input('end'));
		$order->status = $request->input('status');

		$s = $request->input('shipping');
		if ($s != '')
			$order->shipping = $this->decodeDate($s);
		else
			$order->shipping = '';

		$order->save();

		/*
			Se vengono aggiornati i valori di un prodotto, e questo
			prodotto è già contemplato da altri ordini, devo
			duplicarlo per preservare i vecchi valori storici.
			Nel caso, devo accertarmi anche di ricollegare le
			prenotazioni effettuate all'interno di questo ordine con
			la nuova copia del prodotto
		*/

		$products_changed = false;
		$new_products = [];
		$products = $request->input('productid');
		$product_prices = $request->input('productprice');
		$product_transports = $request->input('producttransport');

		for($i = 0; $i < count($products); $i++) {
			$p = Product::findOrFail($products[$i]);
			if ($p->price != $product_prices[$i] || $p->transport != $product_transports[$i]) {
				$old_id = $p->id;

				$p = $p->nextChain();
				$p->price = $product_prices[$i];
				$p->transport = $product_transports[$i];
				$p->save();

				if ($old_id != $p->id) {
					$products_changed = true;

					BookedProduct::whereHas('booking', function($query) use ($order) {
						$query->where('order_id', '=', $order->id);
					})->where('product_id', '=', $old_id)->update(['product_id' => $p->id]);
				}
			}

			$new_products[] = $p->id;
		}

		if ($products_changed == true)
			$order->products()->sync($new_products);

		return $this->successResponse([
			'id' => $order->aggregate->id,
			'header' => $order->aggregate->printableHeader(),
			'url' => url('orders/' . $order->aggregate->id)
		]);
	}

	public function destroy($id)
	{
		DB::beginTransaction();

		$order = Order::findOrFail($id);

		if ($order->supplier->userCan('supplier.orders') == false)
			return $this->errorResponse('Non autorizzato');

		$order->delete();

		return $this->successResponse();
	}

	/*
		Questa funzione è usata per aggiornare manualmente le quantità
		di un certo prodotto all'interno di un ordine
	*/
	public function postFixes(Request $request, $id)
	{
		DB::beginTransaction();

		$order = Order::findOrFail($id);
		if ($order->supplier->userCan('supplier.orders') == false)
			return $this->errorResponse('Non autorizzato');

		$product_id = $request->input('product', []);
		$bookings = $request->input('booking', []);
		$quantities = $request->input('quantity', []);

		for($i = 0; $i < count($bookings); $i++) {
			$booking_id = $bookings[$i];

			$booking = Booking::find($booking_id);
			if ($booking == null)
				continue;
			if ($booking->order->id != $id)
				return $this->errorResponse('Non autorizzato');

			$product = BookedProduct::where('product_id', '=', $product_id)->where('booking_id', '=', $booking_id)->first();
			if ($product == null)
				continue;

			$product->quantity = $quantities[$i];
			$product->save();
		}

		return $this->successResponse();
	}

	public function getSearch(Request $request)
	{
		$supplier_id = $request->input('supplier_id');

		if (empty($supplier_id)) {
			$orders = $this->defaultOrders();
		}
		else {
			$startdate = $this->decodeDate($request->input('startdate'));
			$enddate = $this->decodeDate($request->input('enddate'));

			$supplier = Supplier::find($supplier_id);
			$everything = $supplier->userCan('supplier.orders|supplier.shippings');

			$orders = Aggregate::whereHas('orders', function($query) use ($supplier_id, $startdate, $enddate, $everything) {
				$query->where('supplier_id', '=', $supplier_id)->where('start', '>=', $startdate)->where('end', '<=', $enddate);
				if ($everything == false)
					$query->whereIn('status', ['open', 'shipped', 'archived']);
			})->get();
		}

		return Theme::view('commons.loadablelist', ['identifier' => 'order-list', 'items' => $orders]);
	}
}
