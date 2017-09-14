<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Notifications\BookingNotification;

use Theme;
use DB;

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
            } else {
                if ($a->id == 'new') {
                    $aggr = new Aggregate();
                    $aggr->save();
                    $id = $aggr->id;
                } else {
                    $id = $a->id;
                }

                foreach ($a->orders as $o) {
                    $order = Order::find($o);
                    if ($order->aggregate_id != $id) {
                        $order->aggregate_id = $id;
                        $order->save();
                    }
                }
            }
        }

        return $this->successResponse();
    }

    public function show(Request $request, $id)
    {
        $a = Aggregate::findOrFail($id);

        return Theme::view('order.aggregate', ['aggregate' => $a]);
    }

    public function notify(Request $request, $id)
    {
        $aggregate = Aggregate::findOrFail($id);

        foreach($aggregate->bookings as $booking) {
            if ($booking->status != 'shipped') {
                $booking->user->notify(new BookingNotification($booking));
                usleep(200000);
            }
        }

        $date = date('Y-m-d');

        foreach($aggregate->orders as $order) {
            $order->last_notify = $date;
            $order->save();
        }

        return $aggregate->printableDate('last_notify');
    }
}
