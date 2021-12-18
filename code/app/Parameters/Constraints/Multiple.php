<?php

namespace App\Parameters\Constraints;

use App\Exceptions\InvalidQuantityConstraint;

class Multiple extends Constraint
{
    public function identifier()
    {
        return 'multiple';
    }

    public function printable($product, $order)
    {
        $field = $this->identifier();

        if ($product->$field != 0) {
            return _i('Multiplo: %.02f', $product->$field);
        }

        return null;
    }

    public function test($booked, $quantity)
    {
        $product = $booked->product;

        if ($product->multiple != 0) {
            if (fmod($quantity, $product->multiple) != 0) {
                throw new InvalidQuantityConstraint(_('Quantità non multipla del valore consentito'), 2);
            }
        }
    }
}
