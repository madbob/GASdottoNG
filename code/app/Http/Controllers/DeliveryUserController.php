<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use URL;

use App\Services\BookingsService;
use App\Services\FastBookingsService;

use App\User;
use App\Aggregate;

class DeliveryUserController extends Controller
{
	private $service;

    public function __construct(BookingsService $service)
    {
        $this->middleware('auth');
        $this->service = $service;
    }

    public function show(Request $request, $aggregate_id, $user_id)
    {
        $user = $request->user();
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
        $total = $subject->getValue('delivered', true);

        if ($total == 0) {
            return $this->successResponse();
        }
        else {
            return $this->successResponse([
                'id' => $subject->id,
                'header' => $subject->printableHeader(),
                'url' => route('delivery.user.show', ['delivery' => $aggregate->id, 'user' => $target_user->id]),
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

    public function postFastShipping(Request $request, $aggregate_id)
    {
        $deliverer = $request->user();
        $aggregate = Aggregate::findOrFail($aggregate_id);

        $users = [];
        $selected = $request->input('bookings', []);
        foreach($selected as $user_id) {
            $users[$user_id] = [
                'date' => decodeDate($request->input('date-' . $user_id)),
                'method' => $request->input('method-' . $user_id)
            ];
        }

        $fastshipping = new FastBookingsService();
        $fastshipping->fastShipping($deliverer, $aggregate, $users);
        return $this->successResponse();
    }

    public function objhead2(Request $request, $aggregate_id, $user_id)
    {
        $aggregate = Aggregate::findOrFail($aggregate_id);
        $subject = $aggregate->bookingBy($user_id);

        return response()->json([
            'id' => $subject->id,
            'header' => $subject->printableHeader(),
            'url' => route('delivery.user.show', ['delivery' => $aggregate_id, 'user' => $user_id]),
        ]);
    }
}
