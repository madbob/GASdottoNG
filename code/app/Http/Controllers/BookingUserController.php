<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use URL;
use Auth;

use App\User;
use App\Aggregate;

class BookingUserController extends BookingHandler
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request, $aggregate_id)
    {
        $aggregate = Aggregate::findOrFail($aggregate_id);
        if ($request->user()->can('supplier.shippings', $aggregate) == false) {
            abort(503);
        }

        return view('booking.list', ['aggregate' => $aggregate]);
    }

    public function show(Request $request, $aggregate_id, $user_id)
    {
        $aggregate = Aggregate::findOrFail($aggregate_id);
        if (Auth::user()->id != $user_id && $request->user()->can('supplier.shippings', $aggregate) == false) {
            abort(503);
        }

        $user = User::findOrFail($user_id);

        if ($aggregate->isRunning() || $aggregate->isActive())
            return view('booking.edit', ['aggregate' => $aggregate, 'user' => $user]);
        else
            return view('booking.show', ['aggregate' => $aggregate, 'user' => $user]);
    }

    public function update(Request $request, $aggregate_id, $user_id)
    {
        return $this->bookingUpdate($request, $aggregate_id, $user_id, false);
    }

    public function destroy(Request $request, $aggregate_id, $user_id)
    {
        DB::beginTransaction();

        $user = $request->user();
        $aggregate = Aggregate::findOrFail($aggregate_id);

        if ($user->id != $user_id && $user->can('supplier.shippings', $aggregate) == false) {
            abort(503);
        }

        foreach ($aggregate->orders as $order) {
            $booking = $order->userBooking($user_id, false);
            if ($booking)
                $booking->delete();
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
            'url' => URL::action('BookingUserController@show', ['aggregate' => $aggregate_id, 'user' => $user_id])
        ]);
    }
}
