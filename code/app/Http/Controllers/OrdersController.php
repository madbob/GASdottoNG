<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;
use Log;
use App;

use App\Services\OrdersService;
use App\Product;
use App\Order;
use App\Aggregate;

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

class OrdersController extends BackedController
{
    public function __construct(OrdersService $service)
    {
        $this->service = $service;

        $this->commonInit([
            'reference_class' => 'App\\Order',
            'endpoint' => 'orders',
            'service' => $service,
        ]);
    }

    public function rss(Request $request)
    {
        $aggregates = Aggregate::getByStatus(null, 'open');

        $feed = App::make("feed");
        $feed->title = _i('Ordini Aperti');
        $feed->description = _i('Ordini Aperti');
        $feed->link = $request->url();
        $feed->setDateFormat('datetime');

        if ($aggregates->isEmpty() == false) {
            $feed->pubdate = date('Y-m-d G:i:s');
        }
        else {
            $feed->pubdate = '1970-01-01 00:00:00';
        }

        foreach($aggregates as $aggregate) {
            $summary = '';

            foreach($aggregate->orders as $order) {
                $summary .= $order->printableName() . "<br>\n";

                foreach($order->products as $product) {
                    $summary .= $product->printableName() . "<br>\n";
                }

                $summary .= "<br>\n";
            }

            $feed->addItem([
                'title' => $aggregate->printableName(),
                'author' => $aggregate->gas->first()->printableName(),
                'link' => $aggregate->getBookingURL(),
                'pubdate' => $aggregate->updated_at,
                'description' => nl2br($summary),
            ]);
        }

        return $feed->render('rss');
    }

    public function ical()
    {
        $calendar = new \Eluceo\iCal\Component\Calendar(currentAbsoluteGas()->printableName());

        $orders = Aggregate::defaultOrders(false);
        foreach($orders as $o) {
            if ($o->start && $o->end) {
                $event = new \Eluceo\iCal\Component\Event();
                $event->setDtStart(new \DateTime($o->start))->setDtEnd(new \DateTime($o->end))->setNoTime(true)->setSummary($o->printableName());
                $calendar->addComponent($event);
            }
        }

        header('Content-Type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename="ordini.ics"');
        echo $calendar->render();
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $orders = Aggregate::defaultOrders(!$user->can('order.view', $user->gas));
        return view('pages.orders', ['orders' => $orders]);
    }

    public function show(Request $request, $id)
    {
        $user = $request->user();
        $format = $request->input('format', 'summary');
        $order = $this->service->show($id);

        if ($format == 'summary') {
            $master_summary = $order->aggregate->reduxData();

            if ($request->user()->can('supplier.orders', $order->supplier)) {
                return view('order.summary', ['order' => $order, 'master_summary' => $master_summary]);
            }
            else {
                return view('order.summary_ro', ['order' => $order, 'master_summary' => $master_summary]);
            }
        }
        else {
            abort(404);
        }
    }

    /*
        Questa funzione è usata per aggiornare manualmente le quantità
        di un certo prodotto all'interno di un ordine
    */
    public function getFixes(Request $request, $id, $product_id)
    {
        $order = $this->service->show($id, true);

        $product = Product::findOrFail($product_id);
        if ($order->hasProduct($product) == false) {
            abort(404);
        }

        return view('order.fixes', ['order' => $order, 'product' => $product]);
    }

    public function postFixes(Request $request, $id)
    {
        DB::beginTransaction();

        $order = $this->service->show($id, true);
        $product_id = $request->input('product', []);
        $bookings = $request->input('booking', []);
        $quantities = $request->input('quantity', []);
        $notes = $request->input('notes') ?? '';
        $order->products()->updateExistingPivot($product_id, ['notes' => $notes]);

        for ($i = 0; $i < count($bookings); ++$i) {
            $booking_id = $bookings[$i];

            $booking = Booking::find($booking_id);
            if (is_null($booking) || $booking->order->id != $id) {
                continue;
            }

            $product = $booking->getBooked($product_id, true);
            if ($product->exists && empty($quantities[$i])) {
                $product->delete();
            }
            else if (!empty($quantities[$i])) {
                $product->quantity = $quantities[$i];
                $product->save();
            }
        }

        return $this->successResponse();
    }

    /*
        Questa funzione viene eventualmente attivata da
        AggregatesController::postFeedback()
    */
    public function getFixModifiers(Request $request, $id)
    {
        $order = $this->service->show($id, true);
        return view('order.fixemodifiers', ['order' => $order]);
    }

    public function postFixModifiers(Request $request, $id)
    {
        $action = $request->input('action');

        switch($action) {
            case 'none':
                return $this->successResponse();

            case 'adjust':
                $order = $this->service->show($id, true);
                $aggregate = $order->aggregate;
                $hub = App::make('GlobalScopeHub');

                foreach($aggregate->gas as $gas) {
                    $hub->setGas($gas->id);
                    $redux = $aggregate->reduxData();

                    foreach($aggregate->orders as $order) {
                        foreach($order->bookings as $booking) {
                            $booking->saveModifiers($redux);
                            $booking->fixPayment();
                        }
                    }
                }

                return $this->successResponse();
        }
    }

    public function search(Request $request)
    {
        $startdate = decodeDate($request->input('startdate'));
        $enddate = decodeDate($request->input('enddate'));
        $status = $request->input('status');
        $supplier_id = $request->input('supplier_id');
        $orders = Aggregate::easyFilter($supplier_id, $startdate, $enddate, $status);

        return view('commons.loadablelist', [
            'identifier' => !empty($supplier_id) ? 'order-list-' . $supplier_id : 'order-list',
            'items' => $orders,
            'legend' => (object)[
                'class' => 'Aggregate'
            ],
        ]);
    }

    public function exportModal(Request $request, $id, $type)
    {
        $order = $this->service->show($id);
        return view('order.export' . $type, ['order' => $order]);
    }

    public function document(Request $request, $id, $type)
    {
        return $this->service->document($id, $type, $request->all());
    }
}
