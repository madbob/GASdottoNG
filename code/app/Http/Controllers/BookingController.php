<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Auth;
use Theme;
use URL;

use App\Aggregate;

class BookingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->commonInit([
            'reference_class' => 'App\\Booking'
        ]);
    }

    public function index(Request $request)
    {
        $orders = Aggregate::with('orders')->whereHas('orders', function($query) {
            $query->whereIn('status', ['open', 'closed']);
        })->get();

        $orders = $orders->sort(function($a, $b) {
            return strcmp($a->end, $b->end);
        });

        return Theme::view('pages.bookings', ['orders' => $orders]);
    }

    public function show(Request $request, $id)
    {
        $aggregate = Aggregate::findOrFail($id);
        $user = Auth::user();
        return Theme::view('booking.editwrap', ['aggregate' => $aggregate, 'user' => $user]);
    }

    public function update(Request $request, $id)
    {
        $user_id = Auth::user()->id;
        return $this->bookingUpdate($request, $id, $user_id, false);
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
            $booking = $order->userBooking($user_id);
            $booking->deleteMovements();
            $booking->delete();
        }

        return $this->successResponse();
    }

    public function objhead(Request $request, $id)
    {
        $aggregate = Aggregate::findOrFail($id);

        return response()->json([
            'id' => $aggregate->id,
            'header' => $aggregate->printableUserHeader(),
            'url' => URL::action('BookingController@show', ['id' => $aggregate->id])
        ]);
    }
}
