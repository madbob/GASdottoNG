<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use DB;
use URL;

use App\Services\BookingsService;

use App\User;
use App\Aggregate;
use App\Movement;
use App\MovementType;

class DeliveryUserController extends Controller
{
    public function __construct(BookingsService $service)
    {
        $this->middleware('auth');
        $this->service = $service;
    }

    public function show(Request $request, $aggregate_id, $user_id)
    {
        $user = Auth::user();
        $aggregate = Aggregate::findOrFail($aggregate_id);

        if ($user->id != $user_id && $user->can('supplier.shippings', $aggregate) == false) {
            abort(503);
        }

        $user = User::withTrashed()->findOrFail($user_id);

        return view('delivery.edit', ['aggregate' => $aggregate, 'user' => $user]);
    }

    public function update(Request $request, $aggregate_id, $user_id)
    {
        $target_user = User::find($user_id);
        $aggregate = Aggregate::findOrFail($aggregate_id);

        $this->service->bookingUpdate($request->all(), $aggregate, $target_user, true);

        $subject = $aggregate->bookingBy($target_user->id);
        $subject->generateReceipt();
        $total = $subject->total_delivered;

        if ($total == 0) {
            return $this->successResponse();
        }
        else {
            return $this->successResponse([
                'id' => $subject->id,
                'header' => $subject->printableHeader(),
                'url' => URL::action('DeliveryUserController@show', ['delivery' => $aggregate->id, 'user' => $target_user->id])
            ]);
        }
    }

    public function getFastShipping(Request $request, $aggregate_id)
    {
        $aggregate = Aggregate::findOrFail($aggregate_id);
        if ($request->user()->can('supplier.shippings', $aggregate) == false) {
            abort(503);
        }

        return view('booking.table', ['aggregate' => $aggregate]);
    }

    private function fastShipBooking($deliverer, $booking)
    {
        /*
            Se la prenotazione in oggetto non esiste, salto tutto il resto.
            Altrimenti rischio di creare una prenotazione vuota e salvarla sul
            DB, con tutto quel che ne consegue.
        */
        if ($booking->exists == false) {
            return 0;
        }

        $booking->deliverer_id = $deliverer->id;
        $booking->delivery = date('Y-m-d');

        foreach ($booking->products as $booked) {
            if ($booking->status != 'saved') {
                if ($booked->variants->isEmpty() == false) {
                    foreach($booked->variants as $bpv) {
                        $bpv->delivered = $bpv->true_quantity;
                        $bpv->save();
                    }
                }
                else {
                    $booked->delivered = $booked->true_quantity;
                }
            }

            $booked->final_price = $booked->getValue('delivered');
            $booked->save();
        }

        $booking->status = 'shipped';
        $booking->save();

        $booking->deleteModifiedValues();
        $booking->calculateModifiers(null, true);

        $booking->load('products');
        return $booking->getValue('effective', false, true);
    }

    private function sumFastShippings($aggregate, $user_id)
    {
        $grand_total = 0;

        foreach ($aggregate->orders as $order) {
            $booking = $order->userBooking($user_id);
            $grand_total += $this->fastShipBooking($user, $booking);

            foreach($booking->friends_bookings as $bf) {
                $grand_total += $this->fastShipBooking($user, $bf);
            }
        }

        return $grand_total;
    }

    public function postFastShipping(Request $request, $aggregate_id)
    {
        $user = Auth::user();
        $aggregate = Aggregate::findOrFail($aggregate_id);

        if ($user->can('supplier.shippings', $aggregate) == false) {
            abort(503);
        }

        DB::beginTransaction();

        $users = $request->input('bookings', []);
        $default_payment_method = defaultPaymentByType('booking-payment');

        foreach($users as $index => $user_id) {
            $grand_total = $this->sumFastShippings($aggregate, $user_id);

            if ($grand_total != 0) {
                $subject = $aggregate->bookingBy($user_id);
                $subject->generateReceipt();

                $movement = Movement::generate('booking-payment', $subject->user, $aggregate, $grand_total);
                $movement->method = $request->input('method-' . $user_id, $default_payment_method);
                $movement->save();
            }
        }

        return $this->successResponse();
    }

    public function objhead2(Request $request, $aggregate_id, $user_id)
    {
        $aggregate = Aggregate::findOrFail($aggregate_id);
        $subject = $aggregate->bookingBy($user_id);

        return response()->json([
            'id' => $subject->id,
            'header' => $subject->printableHeader(),
            'url' => URL::action('DeliveryUserController@show', ['delivery' => $aggregate_id, 'user' => $user_id])
        ]);
    }
}
