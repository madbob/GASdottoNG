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
                                $delivered = $request->input($product->id, 0);
                                $booked = $booking->getBooked($product, true);

                                if ($delivered == 0 && $booked->delivered == 0)
                                        continue;

                                $booked->delivered = $delivered;
                                $booked->save();

                                /*
                                if ($product->variants->isEmpty() == false) {
                                        $values = [];

                                        foreach ($product->variants as $variant)
                                                $values[$variant->id] = $request->input($variant->id);

                                        $saved_variants = [];

                                        for ($i = 0; $i < $quantity; $i++) {
                                                $query = BookedProductVariant::where('product_id', '=', $booked->id);

                                                foreach ($values as $variant_id => $vals) {
                                                        $value_id = $vals[$i];

                                                        $query->whereHas('components', function($q) use($variant_id, $value_id) {
                                                                $q->where('variant_id', '=', $variant_id)->where('value_id', '=', $value_id);
                                                        });
                                                }

                                                $query->whereNotIn('id', $saved_variants);
                                                $existing = $query->first();

                                                if ($existing == null) {
                                                        $bpv = new BookedProductVariant();
                                                        $bpv->product_id = $booked->id;
                                                        $bpv->save();

                                                        foreach ($values as $variant_id => $vals) {
                                                                $value_id = $vals[$i];
                                                                $bpc = new BookedProductComponent();
                                                                $bpc->productvariant_id = $bpv->id;
                                                                $bpc->variant_id = $variant_id;
                                                                $bpc->value_id = $value_id;
                                                                $bpc->save();
                                                        }

                                                        $saved_variants[] = $bpv->id;
                                                }
                                                else {
                                                        $saved_variants[] = $existing->id;
                                                }
                                        }

                                        BookedProductVariant::where('product_id', '=', $booked->id)->whereNotIn('id', $saved_variants)->delete();
                                }
                                */
                        }
                }

                return $this->successResponse();
        }

        public function destroy($id)
        {
        //
        }
}
