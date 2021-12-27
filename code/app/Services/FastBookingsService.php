<?php

namespace App\Services;

use DB;

use App\Movement;

class FastBookingsService extends BaseService
{
    private function fastShipProduct($booked)
    {
        if ($booked->variants->isEmpty() == false) {
            foreach($booked->variants as $bpv) {
                $bpv->delivered = $bpv->true_quantity;
                $bpv->save();
            }
        }
        else {
            $booked->delivered = $booked->true_quantity;
            $booked->save();
        }
    }

    private function fastShipBooking($deliverer, $booking)
    {
        /*
            Se la prenotazione in oggetto non esiste, salto tutto il resto.
            Altrimenti rischio di creare una prenotazione vuota e salvarla sul
            DB, con tutto quel che ne consegue.
        */
        if ($booking->exists == false) {
            return 0;
        }

        $booking->deliverer_id = $deliverer->id;
        $booking->delivery = date('Y-m-d');

        if ($booking->status != 'saved') {
            foreach ($booking->products as $booked) {
                $this->fastShipProduct($booked);
            }
        }

        $booking->status = 'shipped';
        $booking->save();

        $booking->saveFinalPrices();
        $booking->saveModifiers();

        $booking->load('products');
        $ret = $booking->getValue('effective', false, true);
        $booking->unsetRelation('products');
        return $ret;
    }

    private function sumFastShippings($deliverer, $booking)
    {
        $grand_total = 0;

        foreach ($booking->bookings as $book) {
            $grand_total += $this->fastShipBooking($deliverer, $book);

            foreach($book->friends_bookings as $bf) {
                $grand_total += $this->fastShipBooking($deliverer, $bf);
            }
        }

        return $grand_total;
    }

    /*
        Se definito, $users è un array associativo che contiene come chiavi gli
        ID degli utenti le cui prenotazioni sono da consegnare e come valori gli
        identificativi per i relativi metodi di pagamento di usare.
        Se viene lasciato a NULL, tutte le prenotazioni sono consegnate con il
        metodo di pagamento di default
    */
    public function fastShipping($deliverer, $aggregate, $users = null)
    {
        DB::beginTransaction();

        $default_payment_method = defaultPaymentByType('booking-payment');
        $bookings = $aggregate->bookings;

        if ($users) {
            $users_ids = array_keys($users);
            $bookings = $bookings->filter(function($booking) use ($users_ids) {
                return in_array($booking->user_id, $users_ids);
            });
        }

        foreach($bookings as $booking) {
            $grand_total = $this->sumFastShippings($deliverer, $booking);

            if ($grand_total != 0) {
                $booking->generateReceipt();

                $movement = Movement::generate('booking-payment', $booking->user, $aggregate, $grand_total);
                $movement->method = $users[$booking->user->id] ?? $default_payment_method;
                $movement->save();
            }
        }

        unset($bookings);
        DB::commit();
    }
}
