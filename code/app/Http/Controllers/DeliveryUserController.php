<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use DB;
use Auth;

use App\User;
use App\Aggregate;

class DeliveryUserController extends Controller
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
                DB::beginTransaction();

                $aggregate = Aggregate::findOrFail($aggregate_id);
                if (Auth::user()->id != $user_id && $aggregate->userCan('supplier.shippings') == false)
                        abort(503);

                $user = User::findOrFail($user_id);

                foreach ($aggregate->orders as $order) {
                        $booking = $order->userBooking($user_id);

                        /*
                                Qui itero comunque tutti i prodotti contemplati
                                nell'ordine, non solo nella prenotazione, in
                                quanti di nuovi potrebbero esserne aggiunti in
                                fase di consegna
                        */
                        foreach ($order->products as $product) {
                                $booked = $booking->getBooked($product, true);

                                if ($product->variants->isEmpty() == false) {
                                        foreach ($booked->variants as $variant) {
                                                $delivered_variant = $request->input($variant->id);
                                                $variant->delivered = $delivered_variant;
                                                $variant->save();

                                                $delivered += $delivered_variant;
                                        }
                                }
                                else {
                                        $delivered = $request->input($product->id, 0);
                                }

                                if ($delivered == 0 && $booked->delivered == 0)
                                        continue;

                                $booked->delivered = $delivered;
                                $booked->save();
                        }
                }

                return $this->successResponse();
        }

        public function destroy($id)
        {
        //
        }
}
