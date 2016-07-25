<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Auth;

use App\User;
use App\Aggregate;

class DeliveryUserController extends BookingHandler
{
        public function show(Request $request, $aggregate_id, $user_id)
        {
                $aggregate = Aggregate::findOrFail($aggregate_id);
                if (Auth::user()->id != $user_id && $aggregate->userCan('supplier.shippings') == false)
                        abort(503);

                $user = User::findOrFail($user_id);
                return view('delivery.edit', ['aggregate' => $aggregate, 'user' => $user]);
        }

        public function update(Request $request, $aggregate_id, $user_id)
        {
                return $this->bookingUpdate($request, $aggregate_id, $user_id, true);
        }

        public function destroy($id)
        {
                //
        }
}
