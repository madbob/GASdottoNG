<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;
use Auth;
use Log;
use URL;

use App\User;
use App\Aggregate;
use App\BookedProductVariant;
use App\BookedProductComponent;

/*
    Questa classe è destinata ad essere estesa dai Controller che maneggiano
    le prenotazioni, ed in particolare il loro aggiornamento.
*/

class BookingHandler extends Controller
{
    public function bookingUpdate(Request $request, $aggregate_id, $user_id, $delivering)
    {
        DB::beginTransaction();

        $user = Auth::user();
        $aggregate = Aggregate::findOrFail($aggregate_id);

        if ($user->id != $user_id && $user->can('supplier.shippings', $aggregate) == false) {
            abort(503);
        }

        if ($delivering == false) {
            $param = 'quantity';
        } else {
            $param = 'delivered';
        }

        foreach ($aggregate->orders as $order) {
            $booking = $order->userBooking($user_id);

            if ($delivering == true) {
                $booking->deliverer_id = Auth::user()->id;
                $booking->delivery = date('Y-m-d');
            }

            $booking->notes = '';
            $booking->save();

            $count_products = 0;

            foreach ($order->products as $product) {
                $quantity = $request->input($product->id, 0);
                $booked = $booking->getBooked($product, true);

                if ($quantity != 0) {
                    $booked->save();

                    if ($product->variants->isEmpty() == false) {
                        $quantity = 0;
                        $quantities = $request->input('variant_quantity_'.$product->id);

                        $values = [];
                        foreach ($product->variants as $variant) {
                            $values[$variant->id] = $request->input('variant_selection_'.$variant->id);
                        }

                        $saved_variants = [];
                        $variant_added = false;

                        for ($i = 0; $i < count($quantities); ++$i) {
                            $q = (float) $quantities[$i];
                            if ($q == 0)
                                continue;

                            $variant_added = true;
                            $query = BookedProductVariant::where('product_id', '=', $booked->id);

                            foreach ($values as $variant_id => $vals) {
                                $value_id = $vals[$i];

                                $query->whereHas('components', function ($q) use ($variant_id, $value_id) {
                                    $q->where('variant_id', '=', $variant_id)->where('value_id', '=', $value_id);
                                });
                            }

                            $query->whereNotIn('id', $saved_variants);
                            $bpv = $query->first();

                            if ($bpv == null) {
                                $bpv = new BookedProductVariant();
                                $bpv->product_id = $booked->id;

                                if ($delivering == false) {
                                    $bpv->quantity = $q;
                                    $bpv->delivered = 0;
                                } else {
                                    $bpv->quantity = 0;
                                    $bpv->delivered = $q;
                                }

                                $bpv->save();

                                foreach ($values as $variant_id => $vals) {
                                    $value_id = $vals[$i];
                                    $bpc = new BookedProductComponent();
                                    $bpc->productvariant_id = $bpv->id;
                                    $bpc->variant_id = $variant_id;
                                    $bpc->value_id = $value_id;
                                    $bpc->save();
                                }
                            }
                            else {
                                if ($bpv->$param != $q) {
                                    $bpv->$param = $q;
                                    $bpv->save();
                                }
                            }

                            $saved_variants[] = $bpv->id;

                            if ($delivering) {
                                $bpv->final_price = $bpv->deliveredValue();
                                $bpv->save();
                            }

                            $quantity += $q;
                        }

                        if ($quantity != 0 && empty($saved_variants)) {
                            Log::error('Prodotto con varianti, prenotazione senza varianti salvate');
                            Log::debug("Dump della richiesta:\n" . print_r($request->all(), true));
                            return $this->errorResponse('Errore nel salvataggio');
                        }

                        BookedProductVariant::where('product_id', '=', $booked->id)->whereNotIn('id', $saved_variants)->delete();

                        /*
                            Per ogni evenienza qui ricarico le varianti appena
                            salvate, affinché il computo del prezzo totale
                            finale per il prodotto risulti corretto
                        */
                        $booked->load('variants');
                    }
                }

                if ($delivering == false && $quantity == 0) {
                    $booked->delete();
                }
                else {
                    if ($quantity != 0)
                        $count_products++;

                    if ($booked->$param != 0 || $quantity != 0) {
                        $booked->$param = $quantity;

                        if ($delivering) {
                            $booked->final_price = $booked->deliveredValue();
                        }

                        $booked->save();
                    }
                }
            }

            if ($delivering == false && $count_products == 0) {
                $booking->delete();
            }
            else {
                if ($delivering) {
                    /*
                        Attenzione!!!
                        Il valore restituito da $booking->check_transport
                        dipende dallo stato della prenotazione, se è "shipped"
                        restituisce... la stessa variabile $booking->transport
                        che vorremmo qui settare!
                        Dunque: prima ricalcolare il costo di trasporto,
                        aggiornato in funzione dei prodotti consegnati (salvati
                        sopra), dopo modificare lo stato
                    */
                    $booking->transport = $booking->check_transport;
                    $booking->status = $request->input('action');
                    $booking->save();
                }
            }
        }

        if ($delivering == false) {
            return $this->successResponse([
                'id' => $aggregate->id,
                'header' => $aggregate->printableUserHeader(),
                'url' => URL::action('BookingController@show', ['id' => $aggregate->id])
            ]);
        }
        else {
            $subject = $aggregate->bookingBy($user_id);

            if ($delivering) {
                $total = $subject->total_delivered;
                $action = 'DeliveryUserController@show';
            }
            else {
                $total = $subject->total_value;
                $action = 'BookingUserController@show';
            }

            if ($total == 0) {
                return $this->successResponse();
            }
            else {
                return $this->successResponse([
                    'id' => $subject->id,
                    'header' => $subject->printableHeader(),
                    'url' => URL::action($action, ['aggregate' => $aggregate_id, 'user' => $user_id])
                ]);
            }
        }
    }
}
