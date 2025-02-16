<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use Log;

use App\Jobs\AggregateSummaries;
use App\Printers\Aggregate as Printer;
use App\Aggregate;
use App\Order;

class AggregatesController extends Controller
{
    public function __construct()
    {
        $this->commonInit([
            'reference_class' => 'App\\Aggregate',
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

    public function store(Request $request)
    {
        DB::beginTransaction();

        $data = $request->input('data');
        $data = json_decode($data);

        foreach ($data as $a) {
            if ($a->id == 'new') {
                $aggr = new Aggregate();
                $aggr->save();
            }
            else {
                $aggr = Aggregate::find($a->id);
                if (is_null($aggr)) {
                    continue;
                }
            }

            $circles = $aggr->circles->pluck('id');

            foreach ($a->orders as $index => $o) {
                $order = Order::find($o);
                if ($order) {
                    $order->aggregate_id = $aggr->id;
                    $order->aggregate_sorting = $index;
                    $order->save();
                    $order->circles()->sync($circles);
                }
            }
        }

        foreach (Aggregate::doesnthave('orders')->get() as $ea) {
            $ea->delete();
        }

        return $this->successResponse();
    }

    public function show(Request $request, $id)
    {
        $a = Aggregate::findOrFail($id);

        return view('order.aggregate', ['aggregate' => $a]);
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        $a = Aggregate::findOrFail($id);
        $a->comment = $request->input('comment', '');
        $a->save();

        $status = $request->input('status', 'no');
        if ($status != 'no') {
            $a->orders()->update(['status' => $status]);
        }

        $circles = array_filter($request->input('circles', []));
        foreach ($a->orders as $o) {
            $o->circles()->sync($circles);
        }

        if ($request->has('change_dates')) {
            $a->orders()->update([
                'start' => decodeDate($request->input('start')),
                'end' => decodeDate($request->input('end')),
                'shipping' => decodeDate($request->input('shipping')),
            ]);
        }

        return $this->successResponse([
            'id' => $a->id,
            'header' => $a->printableHeader(),
            'url' => route('aggregates.show', $a->id),
        ]);
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
        if ($request->user()->can('supplier.shippings', $aggregate) == false) {
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
