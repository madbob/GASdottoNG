<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

use DB;
use App;
use PDF;
use Log;

use ezcArchive;

use App\Jobs\AggregateSummaries;
use App\Aggregate;
use App\Order;
use App\Booking;

class AggregatesController extends OrdersController
{
    public function __construct()
    {
        parent::__construct();

        $this->commonInit([
            'reference_class' => 'App\\Aggregate'
        ]);
    }

    public function create(Request $request)
    {
        $orders = Aggregate::defaultOrders(false);
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

            $deliveries = $aggr->deliveries->pluck('id');

            foreach ($a->orders as $index => $o) {
                $order = Order::find($o);
                if ($order) {
                    $order->aggregate_id = $aggr->id;
                    $order->aggregate_sorting = $index;
                    $order->save();
                    $order->deliveries()->sync($deliveries);
                }
            }
        }

        foreach(Aggregate::doesnthave('orders')->get() as $ea) {
            $ea->delete();
        }

        return $this->successResponse();
    }

    public function show(Request $request, $id)
    {
        DB::beginTransaction();
        $a = Aggregate::findOrFail($id);
        $a->waybackProducts();
        $view = view('order.aggregate', ['aggregate' => $a])->render();
        DB::rollback();
        return $view;
    }

    public function details(Request $request, $id)
    {
        $a = Aggregate::findOrFail($id);
        return view('aggregate.details', ['aggregate' => $a]);
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

        $deliveries = array_filter($request->input('deliveries', []));
        foreach($a->orders as $o) {
            $o->deliveries()->sync($deliveries);
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
        AggregateSummaries::dispatch($id, $message);

        return response()->json((object) [
            'last-notification-date-' . $id => printableDate(date('Y-m-d'))
        ]);
    }

    /*
        Questa funzione serve solo per debuggare le mail di riassunto dei
        prodotti destinate agli utenti
    */
    public function testNotify(Request $request, $id)
    {
        if (env('APP_DEBUG', false) == false) {
            abort(403);
        }

        $aggregate = Aggregate::findOrFail($id);
        $message = $request->input('message', '');

        foreach($aggregate->bookings as $booking) {
            if ($booking->status != 'shipped') {
                echo view('emails.booking', ['booking' => $booking, 'txt_message' => $message])->render();
                echo '<hr>';
            }
        }
    }

    public function document(Request $request, $id, $type)
    {
        $aggregate = Aggregate::findOrFail($id);

        switch ($type) {
            case 'shipping':
                $subtype = $request->input('format', 'pdf');
                $required_fields = $request->input('fields', []);
                $fields = splitFields($required_fields);
                $status = 'booked';

                $shipping_place = $request->input('shipping_place', 'all_by_name');

                $temp_data = [];
                foreach($aggregate->orders as $order) {
                    $temp_data[] = $order->formatShipping($fields, $status, $shipping_place);
                }

                if (empty($temp_data)) {
                    $data = (object) [
                        'headers' => [],
                        'contents' => []
                    ];
                }
                else {
                    $data = (object) [
                        'headers' => $temp_data[0]->headers,
                        'contents' => []
                    ];

                    foreach($temp_data as $td_row) {
                        foreach($td_row->contents as $td) {
                            $found = false;

                            foreach($data->contents as $d) {
                                if ($d->user_id == $td->user_id) {
                                    $d->products = array_merge($d->products, $td->products);
                                    $d->notes = array_merge($d->notes, $td->notes);

                                    foreach($d->totals as $index => $t) {
                                        $d->totals[$index] += $td->totals[$index];
                                    }

                                    $found = true;
                                    break;
                                }
                            }

                            if ($found == false) {
                                $data->contents[] = $td;
                            }
                        }
                    }

                    usort($data->contents, function($a, $b) use ($shipping_place) {
                        if ($shipping_place == 'all_by_place' && $a->shipping_sorting != $b->shipping_sorting) {
                            return $a->shipping_sorting <=> $b->shipping_sorting;
                        }

                        return $a->user_sorting <=> $b->user_sorting;
                    });
                }

                $title = _i('Dettaglio Consegne Ordini');
                $filename = sanitizeFilename($title . '.' . $subtype);
                $temp_file_path = sprintf('%s/%s', sys_get_temp_dir(), $filename);

                if ($subtype == 'pdf') {
                    $pdf = PDF::loadView('documents.order_shipping_pdf', ['fields' => $fields, 'aggregate' => $aggregate, 'data' => $data]);
                    return $pdf->download($filename);
                }
                else if ($subtype == 'csv') {
                    $flat_contents = [];

                    foreach($data->contents as $c) {
                        foreach($c->products as $p) {
                            $flat_contents[] = array_merge($c->user, $p);
                        }
                    }

                    return output_csv($filename, $data->headers, $flat_contents, function($row) {
                        return $row;
                    });
                }

                break;

            case 'summary':
                $subtype = $request->input('format', 'pdf');
                $required_fields = $request->input('fields', []);
                $status = $request->input('status');

                $shipping_place = $request->input('shipping_place', 'all_by_place');
                $data = null;

                foreach($aggregate->orders as $order) {
                    if ($shipping_place == 'all_by_place')
                        $temp_data = $order->formatSummary($required_fields, $status, null);
                    else
                        $temp_data = $order->formatSummary($required_fields, $status, $shipping_place);

                    if (is_null($data)) {
                        $data = $temp_data;
                    }
                    else {
                        $data->contents = array_merge($data->contents, $temp_data->contents);
                    }
                }

                $title = _i('Prodotti Ordini');
                $filename = sanitizeFilename($title . '.' . $subtype);
                $temp_file_path = sprintf('%s/%s', sys_get_temp_dir(), $filename);

                if ($subtype == 'pdf') {
                    $pdf = PDF::loadView('documents.order_summary_pdf', ['aggregate' => $aggregate, 'data' => $data]);
                    return $pdf->download($filename);
                }
                else if ($subtype == 'csv') {
                    return output_csv($filename, $data->headers, $data->contents, function($row) {
                        return $row;
                    });
                }
                else if ($subtype == 'gdxp') {
                    $hub = App::make('GlobalScopeHub');
                    if ($hub->enabled() == false) {
                        $gas = $aggregate->gas->pluck('id');
                    }
                    else {
                        $gas = Arr::wrap($hub->getGas());
                    }

                    $working_dir = sys_get_temp_dir();
                    chdir($working_dir);

                    foreach($gas as $g) {
                        $hub->enable(true);
                        $hub->setGas($g);

                        foreach($aggregate->orders as $order) {
                            /*
                                Attenzione: la funzione document() nomina il
                                file sempre nello stesso modo, a prescindere dal
                                GAS. Se non lo si rinomina in altro modo, le
                                diverse iterazioni sovrascrivono sempre lo
                                stesso file
                            */
                            $f = $order->document('summary', 'gdxp', 'save', null, null, null);
                            $new_f = Str::random(10);
                            rename($f, $new_f);
                            $files[] = $new_f;
                        }
                    }

                    $archivepath = sprintf('%s/prenotazioni.zip', $working_dir, Str::random(10));
                    $archive = ezcArchive::open($archivepath, ezcArchive::ZIP);

                    foreach($files as $f) {
                        $archive->append([$f], '');
                        unlink($f);
                    }

                    return response()->download($archivepath)->deleteFileAfterSend(true);
                }

                break;
        }
    }

    public function multiGAS(Request $request, $id)
    {
        $aggregate = Aggregate::findOrFail($id);
        if ($request->user()->can('supplier.shippings', $aggregate) == false) {
            abort(503);
        }

        return view('order.multigas', ['aggregate' => $aggregate]);
    }
}
