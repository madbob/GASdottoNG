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
                    if ($product->variants->isEmpty() == false) {
                        $quantity = 0;
                        $quantities = $request->input('variant_quantity_'.$product->id);

                        $values = [];
                        foreach ($product->variants as $variant) {
                            $values[$variant->id] = $request->input('variant_selection_'.$variant->id);
                        }

                        $saved_variants = [];

                        for ($i = 0; $i < count($quantities); ++$i) {
                            $q = (float) $quantities[$i];
                            if ($q == 0)
                                continue;

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

                                $saved_variants[] = $bpv->id;
                            } else {
                                if ($bpv->$param != $q) {
                                    $bpv->$param = $q;
                                    $bpv->save();
                                }

                                $saved_variants[] = $bpv->id;
                            }

                            if ($delivering) {
                                $bpv->final_price = $bpv->deliveredValue();
                                $bpv->save();
                            }

                            $quantity += $q;
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

                    if ($booked->$param != $quantity) {
                        $booked->$param = $quantity;

                        if ($delivering) {
                            $booked->final_price = $booked->deliveredValue();
                        }

                        $booked->save();
                    }
                }
            }

            if ($delivering) {
                $booking->status = $request->input('action');
                $booking->save();
            }
            else {
                if ($count_products == 0) {
                    $booking->delete();
                }
            }
        }

        /*
            Da qui ci posso passare in svariati casi, tra cui:
            - creo o modifico una prenotazione dal pannello "Prenotazioni"
            - creo una nuova prenotazione con "Aggiungi Utente" in fase di consegna
            - consegno una prenotazione

            Negli ultimi due casi l'intestazione dell'oggetto (ciò che appare nella loadablelist di riferimento) è
            quella della prenotazione, nel primo è quella dell'ordine. Onde evitare di inviare l'header sbagliato, mi
            aspetto che il client mi dica se si trova in una situazione potenzialmente ambigua per mezzo del parametro
            'booking-on-shipping'. Se non allego informazioni alla risposta, rimane l'header che c'era prima
        */
        $booking_on_shipping = $request->input('booking-on-shipping', null);
        if ($delivering == false && $booking_on_shipping == null) {
            return $this->successResponse();
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
