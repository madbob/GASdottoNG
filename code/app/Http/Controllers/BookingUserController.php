<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use DB;
use Auth;

use App\User;
use App\Aggregate;
use App\BookedProductVariant;
use App\BookedProductComponent;

class BookingUserController extends Controller
{
        public function index(Request $request, $aggregate_id)
        {
                $aggregate = Aggregate::findOrFail($aggregate_id);
                if ($aggregate->userCan('supplier.shippings') == false)
                        abort(503);

                return view('booking.list', ['aggregate' => $aggregate]);
        }

        public function show(Request $request, $aggregate_id, $user_id)
        {
                $aggregate = Aggregate::findOrFail($aggregate_id);
                if (Auth::user()->id != $user_id && $aggregate->userCan('supplier.shippings') == false)
                        abort(503);

                $user = User::findOrFail($user_id);
                return view('booking.edit', ['aggregate' => $aggregate, 'user' => $user]);
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
                        $booking->save();

                        foreach ($order->products as $product) {
                                $quantity = $request->input($product->id, 0);
                                $booked = $booking->getBooked($product, true);

                                if ($product->variants->isEmpty() == false) {
                                        $booked->save();

                                        $quantity = 0;
                                        $quantities = $request->input('variant_quantity');
                                        $values = [];

                                        foreach ($product->variants as $variant)
                                                $values[$variant->id] = $request->input('variant_selection_' . $variant->id);

                                        $saved_variants = [];

                                        for ($i = 0; $i < count($quantities); $i++) {
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
                                                        $bpv->quantity = $quantities[$i];
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
                                                        if ($existing->quantity != $quantities[$i]) {
                                                                $existing->quantity = $quantities[$i];
                                                                $existing->save();
                                                        }

                                                        $saved_variants[] = $existing->id;
                                                }

                                                $quantity += $quantities[$i];
                                        }

                                        BookedProductVariant::where('product_id', '=', $booked->id)->whereNotIn('id', $saved_variants)->delete();
                                }

                                if ($quantity == 0) {
                                        $booked->delete();
                                }
                                else {
                                        if ($booked->quantity != $quantity) {
                                                $booked->quantity = $quantity;
                                                $booked->save();
                                        }
                                }
                        }
                }

                return $this->successResponse();
        }

        public function destroy($id)
        {
        //
        }
}
