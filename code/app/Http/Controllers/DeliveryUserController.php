<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use DB;
use URL;

use App\User;
use App\Aggregate;
use App\Movement;
use App\MovementType;

class DeliveryUserController extends BookingHandler
{
    public function show(Request $request, $aggregate_id, $user_id)
    {
        $user = Auth::user();
        $aggregate = Aggregate::findOrFail($aggregate_id);

        if ($user->id != $user_id && $user->can('supplier.shippings', $aggregate) == false) {
            abort(503);
        }

        $user = User::findOrFail($user_id);

        return view('delivery.edit', ['aggregate' => $aggregate, 'user' => $user]);
    }

    public function update(Request $request, $aggregate_id, $user_id)
    {
        return $this->bookingUpdate($request, $aggregate_id, $user_id, true);
    }

    public function getFastShipping(Request $request, $aggregate_id)
    {
        $aggregate = Aggregate::findOrFail($aggregate_id);
        if ($request->user()->can('supplier.shippings', $aggregate) == false) {
            abort(503);
        }

        return view('booking.table', ['aggregate' => $aggregate]);
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
        $default_payment_method = MovementType::defaultPaymentByType('booking-payment');

        foreach($users as $index => $user_id) {
            $grand_total = 0;

            foreach ($aggregate->orders as $order) {
                $booking = $order->userBooking($user_id);
                $booking->deliverer_id = $user->id;
                $booking->delivery = date('Y-m-d');

                foreach ($booking->products as $booked) {
                    if ($booked->variants->isEmpty() == false) {
                        foreach($booked->variants as $bpv) {
                            $bpv->delivered = $bpv->quantity;
                            $bpv->save();
                        }
                    }
                    else {
                        $booked->delivered = $booked->quantity;
                    }

                    $booked->final_price = $booked->deliveredValue();
                    $booked->save();
                }

                $booking->transport = $booking->check_transport;
                $booking->status = 'shipped';
                $booking->save();

                $booking->load('products');
                $grand_total += $booking->total_value;
            }

            if ($grand_total != 0) {
                $movement = new Movement();
                $movement->type = 'booking-payment';
                $movement->sender_type = 'App\User';
                $movement->sender_id = $user_id;
                $movement->target_type = 'App\Aggregate';
                $movement->target_id = $aggregate_id;
                $movement->method = $request->input('method-' . $user_id, $default_payment_method);
                $movement->amount = $grand_total;
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
