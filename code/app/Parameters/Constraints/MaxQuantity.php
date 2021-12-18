<?php

namespace App\Parameters\Constraints;

class MaxQuantity extends Constraint
{
    public function identifier()
    {
        return 'max_quantity';
    }

    public function printable($product, $order)
    {
        $field = $this->identifier();

        if ($product->$field != 0) {
            return _i('Massimo Consigliato: %.02f', $product->$field);
        }

        return null;
    }

    public function test($booked, $quantity)
    {
        /*
            Il massimo consigliato è, appunto, consigliato. Se la quantità
            prenotata è superiore del previsto, non viene attivato nessun errore
        */
        return;
    }
}
