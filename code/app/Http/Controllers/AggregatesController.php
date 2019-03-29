<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Notifications\BookingNotification;

use DB;
use PDF;
use Log;

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
        $orders = Aggregate::orderBy('id', 'desc')->get();
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
                $id = $aggr->id;
            }
            else {
                $id = $a->id;
            }

            foreach ($a->orders as $index => $o) {
                $order = Order::find($o);
                if ($order) {
                    $order->aggregate_id = $id;
                    $order->aggregate_sorting = $index;
                    $order->save();
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
        $a = Aggregate::findOrFail($id);
        return view('order.aggregate', ['aggregate' => $a]);
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

        if ($aggregate->isActive()) {
            $status = ['pending', 'saved'];
        }
        else {
            $status = ['shipped'];
        }

        foreach($aggregate->bookings as $booking) {
            if (in_array($booking->status, $status)) {
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

    /*
        Questa funzione serve solo per debuggare le mail di riassunto dei
        prodotti destinate agli utenti
    */
    public function testNotify(Request $request, $id)
    {
        if (env('APP_DEBUG', false) == false)
            abort(403);

        $aggregate = Aggregate::findOrFail($id);
        $message = $request->input('message', '');

        foreach($aggregate->bookings as $booking) {
            if ($booking->status != 'shipped') {
                echo view('emails.booking', ['booking' => $booking, 'txt_message' => $message])->render();
                echo '<hr>';
            }
        }
    }

    public function document(Request $request, $id, $type, $subtype = 'none')
    {
        $aggregate = Aggregate::findOrFail($id);

        $shipping_place = $request->input('shipping_place', 'all_by_name');
        $bookings = $aggregate->bookings;
        $bookings = Booking::sortByShippingPlace($bookings, $shipping_place);

        switch ($type) {
            case 'shipping':
                $pdf = PDF::loadView('documents.aggregate_shipping', [
                    'aggregate' => $aggregate,
                    'bookings' => $bookings,
                    'products_source' => 'products_with_friends',
                    'shipping_mode' => $shipping_place
                ]);

                $title = _i('Dettaglio Consegne Ordini');
                $filename = sanitizeFilename($title . '.pdf');
                return $pdf->download($filename);
                break;
        }
    }
}
