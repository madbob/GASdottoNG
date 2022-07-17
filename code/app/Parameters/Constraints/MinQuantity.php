<?php

namespace App\Parameters\Constraints;

use App\Exceptions\InvalidQuantityConstraint;

class MinQuantity extends Constraint
{
    public function identifier()
    {
        return 'min_quantity';
    }

    public function printable($product, $order)
    {
        $field = $this->identifier();

        if ($product->$field > 1) {
            return _i('Minimo: %.02f', $product->$field);
        }

        return null;
    }

    public function test($booked, $quantity)
    {
        $product = $booked->product;

        if ($product->min_quantity != 0) {
            if ($quantity < $product->min_quantity) {
                throw new InvalidQuantityConstraint(_('Quantità inferiore al minimo consentito'), 1);
            }
        }
    }
}
