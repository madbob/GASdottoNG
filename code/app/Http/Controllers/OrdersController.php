<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use DB;
use Auth;
use Theme;

use App\Supplier;
use App\Order;
use App\Aggregate;

/*
	Attenzione, quando si maneggia in questo file bisogna ricordare la distinzione tra Ordine
	e Aggregato.
	Un Ordine fa riferimento ad un fornitore (per il quale l'utente può avere o no permessi
	di modifica) e contiene dei prodotti. Un Aggregato è un insieme di Ordini.
	Per comodità, qui si assume che tutti gli Ordini siano sempre parte di un Aggregato,
	anche se contiene solo l'Ordine stesso. In alcuni casi gli ID passati come parametro alle
	funzioni fanno riferimento ad un Ordine, in altri casi ad un Aggregato.
*/

class OrdersController extends Controller
{
	public function __construct()
	{
		$this->middleware('auth');
	}

	public function index()
	{
		/*
			La selezione degli ordini da visualizzare si può forse fare con una query
			complessa, premesso che bisogna prendere in considerazione i permessi che
			l'utente corrente ha nei confronti dei fornitori degli ordini inclusi
			negli aggregati
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
				if ($order->supplier->userCan('supplier.orders|supplier.bookings|supplier.shippings')) {
					$ok = true;
					break;
				}
			}

			if ($ok == true)
				$orders[] = $aggregate;
		}

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

		$s = $request->input('shipping');
		if ($s != '')
			$o->shipping = $this->decodeDate($s);
		else
			$o->shipping = '';

		if ($o->start > $now)
			$o->status = 'suspended';
		else
			$o->status = 'open';

		$a = new Aggregate();
		$a->save();
		$o->aggregate_id = $a->id;

		$o->id = $o->supplier_id . '::' . $o->start;
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

	public function edit($id)
	{
	//
	}

	public function update(Request $request, $id)
	{
	//
	}

	public function destroy($id)
	{
	//
	}
}
