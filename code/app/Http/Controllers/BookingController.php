<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

use Auth;
use DB;

use App\Aggregate;

class BookingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->commonInit([
            'reference_class' => 'App\\Booking',
        ]);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        if ($user->can('supplier.book', null) === false) {
            return $this->errorResponse(__('generic.unauthorized'));
        }

        /*
            Se l'utente è sospeso, non gli faccio proprio vedere gli ordini
            aperti (così non può interagire)
        */
        if (is_null($user->suspended_at)) {
            $opened = getOrdersByStatus($user, 'open');
        }
        else {
            $opened = new Collection();
        }

        $shipping = getOrdersByStatus($user, 'closed');

        $orders = $opened->merge($shipping)->unique()->sort(function ($a, $b) {
            return strcmp($a->end, $b->end);
        });

        return view('pages.bookings', ['orders' => $orders]);
    }

    public function show(Request $request, $id)
    {
        $aggregate = Aggregate::findOrFail($id);
        $user = Auth::user();

        return view('booking.editwrap', ['aggregate' => $aggregate, 'user' => $user, 'standalone' => false]);
    }

    public function destroy(Request $request, $aggregate_id, $user_id)
    {
        DB::beginTransaction();

        $user = $request->user();
        $aggregate = Aggregate::findOrFail($aggregate_id);

        if ($user->id != $user_id && $user->can('supplier.shippings', $aggregate) === false) {
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
        $aggregate = Aggregate::with(['orders', 'orders.products', 'orders.bookings', 'orders.modifiers'])->findOrFail($id);

        return response()->json([
            'id' => $aggregate->id,
            'header' => $aggregate->printableUserHeader(),
            'url' => route('booking.show', $aggregate->id),
        ]);
    }
}
