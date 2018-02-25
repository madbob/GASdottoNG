<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Notifications\BookingNotification;

use Theme;
use DB;
use PDF;
use Log;

use App\Aggregate;
use App\Order;

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
        $orders = Aggregate::orderBy('id', 'desc')->get();
        return view('order.aggregable', ['orders' => $orders]);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        $data = $request->input('data');
        $data = json_decode($data);

        foreach ($data as $a) {
            if (empty($a->orders)) {
                $aggr = Aggregate::find($a->id);
                if ($aggr != null) {
                    $aggr->delete();
                }
            }
            else {
                if ($a->id == 'new') {
                    $aggr = new Aggregate();
                    $aggr->save();
                    $id = $aggr->id;
                }
                else {
                    $id = $a->id;
                }

                foreach ($a->orders as $o) {
                    $order = Order::find($o);
                    if ($order && $order->aggregate_id != $id) {
                        $order->aggregate_id = $id;
                        $order->save();
                    }
                }
            }
        }

        $empty_aggregates = Aggregate::has('orders', '=', 0)->get();
        foreach($empty_aggregates as $ea) {
            $ea->delete();
        }

        return $this->successResponse();
    }

    public function show(Request $request, $id)
    {
        $a = Aggregate::findOrFail($id);

        return Theme::view('order.aggregate', ['aggregate' => $a]);
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        $a = Aggregate::findOrFail($id);
        $a->comment = $request->input('comment', '');
        $a->save();

        return $this->successResponse([
            'id' => $a->id,
            'header' => $a->printableHeader(),
            'url' => route('aggregates.show', $a->id),
        ]);
    }

    public function notify(Request $request, $id)
    {
        $aggregate = Aggregate::findOrFail($id);
        $message = $request->input('message', '');

        foreach($aggregate->bookings as $booking) {
            if ($booking->status != 'shipped') {
                try {
                    $booking->user->notify(new BookingNotification($booking, $message));
                    usleep(200000);
                }
                catch(\Exception $e) {
                    Log::error('Impossibile inviare notifica mail prenotazione di ' . $booking->user->id);
                }
            }
        }

        $date = date('Y-m-d');

        foreach($aggregate->orders as $order) {
            $order->last_notify = $date;
            $order->save();
        }

        return response()->json((object) [
            'last-notification-date-' . $id => $aggregate->printableDate('last_notify')
        ]);
    }

    public function document(Request $request, $id, $type, $subtype = 'none')
    {
        $aggregate = Aggregate::findOrFail($id);

        switch ($type) {
            case 'shipping':
                $names = [];
                foreach($aggregate->orders as $order) {
                    $names[] = sprintf('%s %s', $order->supplier->name, $order->internal_number);
                }
                $names = join(' / ', $names);

                $html = Theme::view('documents.aggregate_shipping', [
                    'aggregate' => $aggregate,
                    'bookings' => $aggregate->bookings,
                    'products_source' => 'products_with_friends'
                ])->render();

                $filename = sprintf('Dettaglio Consegne ordini %s.pdf', $names);
                PDF::SetTitle(sprintf('Dettaglio Consegne ordini %s', $names));
                PDF::AddPage();
                PDF::writeHTML($html, true, false, true, false, '');
                PDF::Output($filename, 'D');
                break;
        }
    }
}
