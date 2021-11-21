<?php

namespace App\Parameters\MovementType;

use App\Movement;

class BookingPayment extends MovementType
{
    public function identifier()
    {
        return 'booking-payment';
    }

    public function initNew($type)
    {
        $type->name = 'Pagamento prenotazione da parte di un socio';
        $type->sender_type = 'App\User';
        $type->target_type = 'App\Booking';
        $type->visibility = false;
        $type->system = true;

        $type->function = json_encode($this->voidFunctions([
            (object) [
                'method' => 'cash',
                'target' => $this->format(['bank' => 'increment']),
                'master' => $this->format(['cash' => 'increment']),
            ],
            (object) [
                'method' => 'credit',
                'sender' => $this->format(['bank' => 'decrement']),
                'target' => $this->format(['bank' => 'increment']),
            ],
        ]));

        return $type;
    }

    public function systemInit($mov)
    {
        $mov->callbacks = [
            /*
                Il problema di fondo è che, a livello utente, un aggregato riceve un solo pagamento, dunque
                devo a posteriori dividere tale pagamento tra le prenotazioni al suo interno creando
                movimenti individuali.
                Qui assumo che l'ammontare pagato per ciascuna prenotazione corrisponda col totale consegnato
                della prenotazione stessa
            */
            'pre' => function (Movement $movement) {
                if ($movement->target_type == 'App\Aggregate') {
                    $total = $movement->amount;
                    $aggregate = $movement->target;
                    $user = $movement->sender;
                    $m = null;

                    /*
                        'handling_status' è un attributo fittizio allegato all'oggetto solo per determinare lo
                        stato corrente della consegna. Cfr. la callback parse()
                    */
                    $handling_status = $movement->handling_status;
                    unset($movement->handling_status);

                    foreach ($aggregate->orders as $order) {
                        $booking = $order->userBooking($user);
                        if ($booking->exists == false) {
                            /*
                                Quando un utente non ha fatto nessuna prenotazione, ma
                                i suoi amici si, non ho un soggetto cui agganciare il
                                pagamento. Dunque lo creo qui al volo.
                                Tanto comunque sarebbe creato, dopo, da
                                DeliveryUserController::update() (quando marcato come
                                consegnato), dunque tanto vale farlo subito
                            */
                            if ($booking->friends_bookings->isEmpty()) {
                                continue;
                            }
                            else {
                                $booking->save();
                            }
                        }

                        if (isset($handling_status->{$booking->id})) {
                            $delivered = $handling_status->{$booking->id};
                        }
                        else {
                            $delivered = $booking->getValue('effective', true, true);
                        }

                        if ($total < $delivered) {
                            $delivered = $total;
                        }

                        $existing_movement = $booking->payment;
                        $date = $movement->date;

                        if (is_null($existing_movement)) {
                            $m = $movement->replicate();
                            $m->target_id = $booking->id;
                            $m->target_type = 'App\Booking';

                            /*
                                Qui devo ricaricare la relazione "target",
                                altrimenti resta in memoria quella precedente
                                (che faceva riferimento ad un Aggregate, dunque
                                non è corretta e sul salvataggio spacca tutto)
                            */
                            $m->load('target');
                        }
                        else {
                            $m = $existing_movement;
                        }

                        $m->date = $date;
                        $m->amount = $delivered;
                        $m->save();

                        $total -= $delivered;
                        $total = max(0, $total);
                    }

                    /*
                        Se avanza qualcosa, lo metto sulla fiducia nell'ultimo movimento salvato
                    */
                    if ($total > 0 && $m != null) {
                        $m->amount += $total;
                        $m->save();
                    }

                    return 2;
                }

                return 1;
            },
            'post' => function (Movement $movement) {
                $target = $movement->target;
                if ($target != null) {
                    /*
                        Salvando il movimento contabile legato ad
                        una consegna, ne aggiorno anche il suo stato.
                        cfr. BookingHandler::bookingUpdate();
                    */
                    $target->payment_id = $movement->id;
                    $target->status = 'shipped';
                    $target->save();
                }
            },
            'parse' => function (Movement &$movement, $request) {
                if ($movement->target_type == 'App\Aggregate') {
                    if (isset($request['delivering-status'])) {
                        $movement->handling_status = json_decode($request['delivering-status']);
                    }
                }
            },
            'delete' => function(Movement $movement) {
                $movement->detachFromTarget();
            }
        ];

        return $mov;
    }
}
