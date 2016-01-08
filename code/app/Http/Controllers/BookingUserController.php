<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use DB;

use App\User;
use App\Aggregate;
use App\BookedProductVariant;
use App\BookedProductComponent;

class BookingUserController extends Controller
{
        public function index()
        {

        }

        public function create()
        {
        //
        }

        public function store(Request $request)
        {
        //
        }

        public function show(Request $request, $aggregate_id, $user_id)
        {
                /*
                        TODO    Verificare permessi
                */
                $aggregate = Aggregate::findOrFail($aggregate_id);
                $user = User::findOrFail($user_id);
                return view('booking.edit', ['aggregate' => $aggregate, 'user' => $user]);
        }

        public function edit($id)
        {
        //
        }

        public function update(Request $request, $aggregate_id, $user_id)
        {
                DB::beginTransaction();

                /*
                        TODO    Verificare permessi
                */
                $aggregate = Aggregate::findOrFail($aggregate_id);
                $user = User::findOrFail($user_id);

                foreach ($aggregate->orders as $order) {
                        $booking = $order->userBooking($user_id);
                        $booking->save();

                        foreach ($order->products as $product) {
                                $quantity = $request->input($product->id, 0);
                                $booked = $booking->getBooked($product, true);

                                if ($quantity == 0) {
                                        $booked->delete();
                                }
                                else {
                                        $booked->quantity = $quantity;
                                        $booked->save();

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
