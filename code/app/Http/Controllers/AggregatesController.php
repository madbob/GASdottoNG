<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use Log;

use App\Services\AggregatesService;
use App\Jobs\AggregateSummaries;
use App\Printers\Aggregate as Printer;
use App\Aggregate;
use App\Order;

class AggregatesController extends BackedController
{
    public function __construct(AggregatesService $service)
    {
        $this->service = $service;

        $this->commonInit([
            'reference_class' => Aggregate::class,
            'service' => $service,
        ]);
    }

    public function index()
    {
        return redirect()->route('orders.index');
    }

    public function create(Request $request)
    {
        $orders = defaultOrders(false);

        return view('order.aggregable', ['orders' => $orders]);
    }

    public function show(Request $request, $id)
    {
        $a = Aggregate::findOrFail($id);

        return view('order.aggregate', ['aggregate' => $a]);
    }

    public function notify(Request $request, $id)
    {
        $message = $request->input('message', '');

        try {
            AggregateSummaries::dispatch($id, $message);
        }
        catch (\Exception $e) {
            Log::error('Unable to trigger AggregateSummaries job on aggregate notification: ' . $e->getMessage());
        }

        return response()->json((object) [
            'last-notification-date-' . $id => printableDate(date('Y-m-d')),
        ]);
    }

    public function exportModal(Request $request, $id, $type)
    {
        $aggregate = Aggregate::findOrFail($id);

        return view('aggregate.export' . $type, ['aggregate' => $aggregate]);
    }

    /*
        Questa funzione, invocata dopo il salvataggio di un ordine, deve
        ritornare un array di URL da cui attingere modali di interazione con
        l'utente per svolgere eventuali funzioni secondarie.
        Viene invocata dalla funzione JS afterAggregateChange()
    */
    public function postFeedback(Request $request, $id)
    {
        $ret = [];
        $aggregate = Aggregate::findOrFail($id);
        $master_summary = null;

        /*
            Se l'ordine non è più attivo (e dunque risulta consegnato e
            archiviato), include dei modificatori calcolati in modo trasversale
            tra le prenotazioni (e.g. le spese di trasporto in valore assoluto,
            da ripartire in funzione del valore delle prenotazioni) e la
            ripartizione effettuata in base al prenotato non è coerente con
            quella reale, si attiva la funzione di revisione dei modificatori
        */
        if ($aggregate->isActive() === false) {
            foreach ($aggregate->orders as $order) {
                $modifiers = $order->involvedModifiers(true);

                foreach ($modifiers as $modifier) {
                    if ($modifier->isTrasversal()) {
                        if (is_null($master_summary)) {
                            $master_summary = $aggregate->reduxData();
                        }

                        $broken = $order->unalignedModifiers($master_summary);

                        if (! empty($broken)) {
                            $ret[] = route('orders.fixmodifiers', $order->id);
                            break;
                        }
                    }
                }
            }
        }

        return response()->json($ret);
    }

    public function multiGAS(Request $request, $id)
    {
        $aggregate = Aggregate::findOrFail($id);
        if ($request->user()->can('supplier.shippings', $aggregate) === false) {
            abort(503);
        }

        return view('order.multigas', ['aggregate' => $aggregate]);
    }

    public function document(Request $request, $id, $type)
    {
        $printer = new Printer();
        $aggregate = Aggregate::findOrFail($id);

        return $printer->document($aggregate, $type, $request->all());
    }
}
