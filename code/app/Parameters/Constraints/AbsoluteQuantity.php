<?php

namespace App\Parameters\Constraints;

use App\Exceptions\InvalidQuantityConstraint;

class AbsoluteQuantity extends Constraint
{
    public function identifier()
    {
        return 'absolute';
    }

    public function mandatoryContraint(): bool
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
        if ($quantity > 9999.99) {
            throw new InvalidQuantityConstraint(_i('La quantità massima è 9999.99'), 5);
        }
    }
}
