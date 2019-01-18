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
        $target_user = User::find($user_id);
        $aggregate = Aggregate::findOrFail($aggregate_id);

        if ($target_user->testUserAccess() == false && $user->can('supplier.shippings', $aggregate) == false) {
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

            if ($request->has('notes_' . $order->id) && $request->input('notes_' . $order->id) != null)
                $booking->notes = $request->input('notes_' . $order->id);

            $booking->save();

            $count_products = 0;

            foreach ($order->products as $product) {
                $quantity = $request->input($product->id, 0);
                if (empty($quantity))
                    $quantity = 0;

                $booked = $booking->getBooked($product, true);

                if ($quantity != 0) {
                    $booked->save();

                    if ($product->variants->isEmpty() == false) {
                        $quantity = 0;

                        $quantities = $request->input('variant_quantity_' . $product->id);
                        if (empty($quantities))
                            continue;

                        $values = [];
                        foreach ($product->variants as $variant) {
                            $values[$variant->id] = $request->input('variant_selection_'.$variant->id);
                        }

                        $saved_variants = [];

                        for ($i = 0; $i < count($quantities); ++$i) {
                            $q = (float) $quantities[$i];

                            $query = BookedProductVariant::where('product_id', '=', $booked->id);

                            foreach ($values as $variant_id => $vals) {
                                $value_id = $vals[$i];

                                $query->whereHas('components', function ($q) use ($variant_id, $value_id) {
                                    $q->where('variant_id', '=', $variant_id)->where('value_id', '=', $value_id);
                                });
                            }

                            $query->whereNotIn('id', $saved_variants);
                            $bpv = $query->first();

                            if (is_null($bpv)) {
                                if ($q == 0)
                                    continue;

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

                                $no_components = true;

                                foreach ($values as $variant_id => $vals) {
                                    $value_id = $vals[$i];
                                    if (empty($value_id))
                                        continue;
                                    $bpc = new BookedProductComponent();
                                    $bpc->productvariant_id = $bpv->id;
                                    $bpc->variant_id = $variant_id;
                                    $bpc->value_id = $value_id;
                                    $bpc->save();
                                    $no_components = false;
                                }

                                if ($no_components)
                                    $bpv->delete();
                            }
                            else {
                                if ($q == 0 && $delivering == false) {
                                    $bpv->delete();
                                    continue;
                                }

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

                        /*
                            Attenzione: in fase di consegna/salvataggio è lecito
                            che una quantità sia a zero, ma ciò non implica
                            eliminare la variante
                        */
                        if ($delivering == false)
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
                    $booking->distributeTransport();

                    $new_status = $request->input('action');
                    if ($new_status == 'saved' && $booking->payment != null) {
                        $booking->payment->delete();
                        $booking->payment_id = null;
                    }

                    $booking->status = $new_status;
                    $booking->save();

                    foreach($booking->friends_bookings as $friend_booking) {
                        $friend_booking->status = $new_status;
                        $friend_booking->save();
                    }
                }
            }
        }

        /*
            In contesti diversi ritorno risposte diverse, da cui dipende
            l'header che verrà visualizzato chiudendo il pannello su cui si è
            operato
        */
        if ($delivering == false) {
            if ($user_id != $user->id && $target_user->isFriend() && $target_user->parent_id == $user->id) {
                /*
                    Ho effettuato una prenotazione per un amico
                */
                return $this->successResponse([
                    'id' => $aggregate->id,
                    'header' => $target_user->printableFriendHeader($aggregate),
                    'url' => URL::action('BookingUserController@show', ['aggregate_id' => $aggregate_id, 'user_id' => $user_id])
                ]);
            }
            else {
                /*
                    Ho effettuato una prenotazione per me o per un utente di
                    primo livello (non un amico)
                */
                return $this->successResponse([
                    'id' => $aggregate->id,
                    'header' => $aggregate->printableUserHeader(),
                    'url' => URL::action('BookingController@show', ['id' => $aggregate->id])
                ]);
            }
        }
        else {
            $subject = $aggregate->bookingBy($user_id);
            $subject->generateReceipt();

            $total = $subject->total_delivered;

            if ($total == 0) {
                return $this->successResponse();
            }
            else {
                $action = 'DeliveryUserController@show';
                return $this->successResponse([
                    'id' => $subject->id,
                    'header' => $subject->printableHeader(),
                    'url' => URL::action($action, ['aggregate' => $aggregate_id, 'user' => $user_id])
                ]);
            }
        }
    }
}
