<?php

namespace App\Parameters\Constraints;

use App\Exceptions\InvalidQuantityConstraint;

class DiscreteUnit extends Constraint
{
    public function identifier()
    {
        return 'discrete';
    }

    public function mandatoryContraint()
    {
        return true;
    }

    public function printable($product, $order)
    {
        /*
            Questo contraint è assoluto, vale per tutti i prodotti e non viene
            esplicitamente visualizzato
        */
        return null;
    }

    public function test($booked, $quantity)
    {
        if ($booked->product->measure->discrete) {
            if (filter_var((float) $quantity, FILTER_VALIDATE_INT) === false) {
                throw new InvalidQuantityConstraint(_i('La quantità di questo prodotto deve essere intera'), 6);
            }
        }
    }
}
