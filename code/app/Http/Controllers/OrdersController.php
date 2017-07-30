<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Auth;
use Theme;
use PDF;
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

        $this->commonInit([
            'reference_class' => 'App\\Order'
        ]);
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

        $user = Auth::user();
        $aggregates = Aggregate::with('orders')->get();

        foreach ($aggregates as $aggregate) {
            $ok = false;

            foreach ($aggregate->orders as $order) {
                if ($order->status == 'open') {
                    $ok = true;
                    break;
                }

                if ($order->status != 'archived' && ($user->can('supplier.orders', $order->supplier) || $user->can('supplier.shippings', $order->supplier))) {
                    $ok = true;
                    break;
                }
            }

            if ($ok == true) {
                $orders[] = $aggregate;
            }
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
        if ($request->user()->can('supplier.orders', $supplier) == false) {
            return $this->errorResponse('Non autorizzato');
        }

        $o = new Order();
        $o->supplier_id = $request->input('supplier_id');

        $now = date('Y-m-d');
        $o->comment = $request->input('comment');
        $o->start = decodeDate($request->input('start'));
        $o->end = decodeDate($request->input('end'));
        $o->status = $request->input('status');

        $s = $request->input('shipping');
        if ($s != '') {
            $o->shipping = decodeDate($s);
        } else {
            $o->shipping = '';
        }

        $a = new Aggregate();
        $a->save();

        $o->aggregate_id = $a->id;
        $o->save();

        $o->products()->sync($supplier->products()->where('active', '=', true)->where('archived', '=', false)->get());

        return $this->successResponse([
            'id' => $a->id,
            'header' => $a->printableHeader(),
            'url' => url('aggregates/'.$a->id),
        ]);
    }

    public function show(Request $request, $id)
    {
        $order = Order::findOrFail($id);
        $summary = $order->calculateSummary();
        return response()->json($summary);
    }

    public function recalculate(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $final_products = [];
        $enabled = $request->input('enabled', []);
        $prices = $request->input('product_price', []);
        $transports = $request->input('product_transport', []);
        $availables = $request->input('product_max_available', []);
        $products = $request->input('productid');

        for ($i = 0; $i < count($products); ++$i) {
            $id = $products[$i];

            foreach ($enabled as $en) {
                if ($en == $id) {
                    $prod = Product::find($id);
                    $prod->price = $prices[$i];
                    $prod->transport = $transports[$i];
                    $prod->max_available = $availables[$i];
                    $final_products[] = $prod;
                    break;
                }
            }
        }

        $summary = $order->calculateSummary($final_products);
        return response()->json($summary);
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        $order = Order::findOrFail($id);
        if ($request->user()->can('supplier.orders', $order->supplier) == false) {
            return $this->errorResponse('Non autorizzato');
        }

        $order->comment = $request->input('comment');
        $order->start = decodeDate($request->input('start'));
        $order->end = decodeDate($request->input('end'));
        $order->status = $request->input('status');
        $order->discount = $request->input('discount');
        $order->transport = $request->input('transport');

        $s = $request->input('shipping');
        if ($s != '') {
            $order->shipping = decodeDate($s);
        } else {
            $order->shipping = '';
        }

        $order->save();

        $new_products = [];
        $enabled = $request->input('enabled', []);
        $prices = $request->input('product_price', []);
        $transports = $request->input('product_transport', []);
        $availables = $request->input('product_max_available', []);
        $products = $request->input('productid');

        for ($i = 0; $i < count($products); ++$i) {
            $id = $products[$i];

            foreach ($enabled as $en) {
                if ($en == $id) {
                    $new_products[] = $id;
                    break;
                }
            }

            $prod = Product::find($id);
            if ($prod->price != $prices[$i] || $prod->transport != $transports[$i] || $prod->max_available != $availables[$i]) {
                $prod->price = $prices[$i];
                $prod->transport = $transports[$i];
                $prod->max_available = $availables[$i];
                $prod->save();
            }
        }

        /*
            Se vengono rimossi dei prodotti dall'ordine, ne elimino tutte le
            relative prenotazioni sinora avvenute
        */
        $removed_products = $order->products()->whereNotIn('id', $new_products)->lists('id')->toArray();
        foreach($order->bookings as $booking) {
            $booking->products()->whereIn('product_id', $removed_products)->delete();
            if ($booking->products->isEmpty())
                $booking->delete();
        }

        $order->products()->sync($new_products);

        $discounted = $request->input('discounted', []);
        foreach ($order->products as $product) {
            $dis = false;

            foreach ($discounted as $en) {
                if ($en == $product->id) {
                    $dis = true;
                    break;
                }
            }

            if ($product->pivot->discount_enabled != $dis) {
                $order->products()->updateExistingPivot($product->id, ['discount_enabled' => $dis]);
            }
        }

        return $this->successResponse([
            'id' => $order->aggregate->id,
            'header' => $order->aggregate->printableHeader(),
            'url' => url('aggregates/'.$order->aggregate->id),
        ]);
    }

    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        $order = Order::findOrFail($id);

        if ($request->user()->can('supplier.orders', $order->supplier) == false) {
            return $this->errorResponse('Non autorizzato');
        }

        foreach($order->bookings as $booking)
            $booking->deleteMovements();
        $order->deleteMovements();

        $order->delete();

        return $this->successResponse();
    }

    /*
        Questa funzione è usata per aggiornare manualmente le quantità
        di un certo prodotto all'interno di un ordine
    */
    public function fixes(Request $request, $id)
    {
        DB::beginTransaction();

        $order = Order::findOrFail($id);
        if ($request->user()->can('supplier.orders', $order->supplier) == false) {
            return $this->errorResponse('Non autorizzato');
        }

        $product_id = $request->input('product', []);
        $bookings = $request->input('booking', []);
        $quantities = $request->input('quantity', []);

        for ($i = 0; $i < count($bookings); ++$i) {
            $booking_id = $bookings[$i];

            $booking = Booking::find($booking_id);
            if ($booking == null) {
                continue;
            }

            if ($booking->order->id != $id) {
                return $this->errorResponse('Non autorizzato');
            }

            $product = $booking->getBooked($product_id, true);
            $product->quantity = $quantities[$i];
            $product->save();
        }

        return $this->successResponse();
    }

    public function search(Request $request)
    {
        $supplier_id = $request->input('supplier_id');

        if (empty($supplier_id)) {
            $orders = $this->defaultOrders();
        }
        else {
            if ($request->has('startdate') && $request->has('enddate')) {
                $startdate = decodeDate($request->input('startdate'));
                $enddate = decodeDate($request->input('enddate'));

                $supplier = Supplier::find($supplier_id);
                $everything = ($request->user()->can('supplier.orders', $supplier) || $request->user()->can('supplier.shippings', $supplier));

                $orders = Aggregate::whereHas('orders', function ($query) use ($supplier_id, $startdate, $enddate, $everything) {
                    $query->where('supplier_id', '=', $supplier_id)->where('start', '>=', $startdate)->where('end', '<=', $enddate);
                    if ($everything == false) {
                        $query->whereIn('status', ['open', 'shipped', 'archived']);
                    }
                })->get();
            }
            else {
                $supplier = Supplier::find($supplier_id);
                $orders = $supplier->aggregates->take(10)->get();
            }
        }

        $list_identifier = $request->input('list_identifier', 'order-list');
        return Theme::view('commons.loadablelist', ['identifier' => $list_identifier, 'items' => $orders]);
    }

    public function document(Request $request, $id, $type, $subtype = 'none')
    {
        $order = Order::findOrFail($id);

        switch ($type) {
            case 'shipping':
                $html = Theme::view('documents.order_shipping', ['order' => $order])->render();
                $filename = sprintf('Dettaglio Consegne %s.pdf', $order->supplier->name);
                PDF::SetTitle(sprintf('Dettaglio Consegne %s del %s', $order->supplier->name, date('d/m/Y')));
                PDF::AddPage();
                PDF::writeHTML($html, true, false, true, false, '');
                PDF::Output($filename, 'D');
                break;

            case 'summary':
                if ($subtype == 'pdf') {
                    $html = Theme::view('documents.order_summary_pdf', ['order' => $order])->render();
                    $filename = sprintf('Prodotti ordinati %s.pdf', $order->supplier->name);
                    PDF::SetTitle(sprintf('Prodotti ordinati %s del %s', $order->supplier->name, date('d/m/Y')));
                    PDF::AddPage();
                    PDF::writeHTML($html, true, false, true, false, '');
                    PDF::Output($filename, 'D');
                }
                else if ($subtype == 'csv') {
                    $filename = sprintf('Prodotti ordinati %s.csv', $order->supplier->name);
                    http_csv_headers($filename);
                    return Theme::view('documents.order_summary_csv', ['order' => $order]);
                }
                break;

            case 'table':
                $filename = sprintf('Tabella Ordine %s.csv', $order->supplier->name);
                http_csv_headers($filename);
                if ($subtype == 'booked')
                    return Theme::view('documents.order_table_booked', ['order' => $order]);
                else if ($subtype == 'delivered')
                    return Theme::view('documents.order_table_delivered', ['order' => $order]);
                break;
        }
    }
}
