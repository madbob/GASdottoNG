<?php

/*
    Il problema a monte è che i modificatori applicati complessivamente su un
    ordine (o un aggregato) che vengono distribuiti tra le prenotazioni in modo
    dinamico, in base al valore o al peso, sono sommariamente calcolati in fase
    di consegna ma il reale valore può essere determinato solo quando tutte le
    quantità consegnate sono note.
    Dunque questo comando viene lanciato quando un ordine viene messo nello
    stato "Consegnato" (e più precisamente viene eseguito quando tutti gli
    ordini in un aggregato sono Consegnati) e ricalcola tutti i modificatori,
    assumendo che tutte le consegne siano state salvate e dunque la corretta
    distribuzione possa essere determinata.
*/

namespace App\Jobs;

use Log;

use App\Order;

class ReviewDeliveries extends Job
{
    public $order_id;

    public function __construct($order_id)
    {
        parent::__construct();
        $this->order_id = $order_id;
    }

    protected function realHandle()
    {
        $order = Order::find($this->order_id);

        if ($order->aggregate->status == 'shipped') {
            foreach($aggregate->gas as $gas) {
                $this->hub->setGas($gas->id);
                $redux = $aggregate->reduxData();

                foreach($aggregate->orders as $order) {
                    foreach($order->bookings as $booking) {
                        $booking->saveModifiers($redux);
                    }
                }
            }
        }
    }
}
