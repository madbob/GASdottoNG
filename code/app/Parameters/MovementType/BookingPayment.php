<?php

namespace App\Parameters\MovementType;

use App\Movement;
use App\User;
use App\Booking;
use App\Aggregate;

class BookingPayment extends MovementType
{
    public function identifier()
    {
        return 'booking-payment';
    }

    public function initNew($type)
    {
        $type->name = __('texts.movements.defaults.booking');
        $type->sender_type = User::class;
        $type->target_type = Booking::class;
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
                'is_default' => true,
            ],
        ]));

        return $type;
    }

    private function handleModifiers($movement, $booking)
    {
        /*
            Qui evito anche solo di tentare di correggere eventuali movimenti
            correlati esistenti: non faccio assunzioni sullo stato dei
            modificatori al momento della creazione del primo movimento,
            rigenero tutto daccapo e fine.
            Questo va considerato qualora i movimenti correlati fossero
            utilizzati anche per altri scopi, oltre i modificatori.
        */
        foreach ($movement->related as $rel) {
            $rel->delete();
        }

        $values = $booking->allModifiedValues(null, false);
        $amount = $movement->amount;

        foreach ($values as $value) {
            $movement_type = $value->modifier->movementType;
            if (is_null($movement_type)) {
                $amount = $value->sumAmount($amount);
            }
            else {
                $value->generateMovement($movement);
            }
        }

        return $amount;
    }

    public function systemInit($mov)
    {
        $mov->callbacks = [
            'pre' => function (Movement $movement) {
                /*
                    Il problema di fondo è che, a livello utente, un aggregato
                    riceve un solo pagamento, dunque devo a posteriori dividere
                    tale pagamento tra le prenotazioni al suo interno creando
                    movimenti individuali.
                    Qui assumo che l'ammontare pagato per ciascuna prenotazione
                    corrisponda col totale consegnato della prenotazione stessa
                */
                if ($movement->target_type == Aggregate::class) {
                    $aggregate = $movement->target;
                    $user = $movement->sender;
                    $m = null;

                    foreach ($aggregate->orders as $order) {
                        $booking = $order->userBooking($user);
                        if ($booking->exists === false) {
                            /*
                                Quando un utente non ha fatto nessuna
                                prenotazione, ma i suoi amici si, non ho un
                                soggetto cui agganciare il pagamento. Dunque lo
                                creo qui al volo.
                                Tanto comunque sarebbe creato, dopo, da
                                DeliveryUserController::update() (quando marcato
                                come consegnato), dunque tanto vale farlo subito
                            */
                            if ($booking->friends_bookings->isEmpty()) {
                                continue;
                            }
                            else {
                                $booking->save();
                            }
                        }

                        /*
                            - calcolo il valore del prenotato
                            - creo il relativo movimento
                            - itero i modificatori (che a questo punto devono
                              già essere tutti stati calcolati e assegnati alla
                              prenotazione)
                            - se il modificatore prevede un tipo movimento
                              specifico creo un altro movimento, altrimenti
                              sommo il totale a quello già creato
                        */

                        $delivered = $booking->getValue('delivered', true, true);
                        $existing_movement = $booking->payment;
                        $date = $movement->date;

                        if (is_null($existing_movement)) {
                            $m = $movement->replicate();
                            $m->target_id = $booking->id;
                            $m->target_type = Booking::class;

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

                        $after_delivered = $this->handleModifiers($m, $booking);
                        if ($after_delivered != $delivered) {
                            $m->amount = $after_delivered;
                            $m->save();
                            $delivered = $after_delivered;
                        }
                    }

                    return 2;
                }

                /*
                    Nota bene: questa funzione fa qualcosa solo quando arriva il
                    pagamento di una prenotazione riferito ad un intero
                    aggregato.
                    In caso di aggiornamento del pagamento di un Booking, è
                    comunque consigliato triggerare sempre il salvataggio del
                    pagamento su tutto l'aggregato affinché vengano contemplati
                    tutti i casi possibili (modificatori, movimenti contabili
                    separati e altro)
                */

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

                    foreach ($target->friends_bookings as $friend_booking) {
                        $friend_booking->status = 'shipped';
                        $friend_booking->save();
                    }
                }
            },
            'delete' => function (Movement $movement) {
                $movement->detachFromTarget();
            },
        ];

        return $mov;
    }
}
