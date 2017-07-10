<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use URL;

use App\User;
use App\Aggregate;

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
