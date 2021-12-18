<?php

namespace App\Parameters\Constraints;

use App\Exceptions\AnnotatedQuantityConstraint;

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

    public function hardContraint()
    {
        return false;
    }

    public function test($booked, $quantity)
    {
        $product = $booked->product;

        if ($product->max_quantity != 0) {
            if ($quantity > $product->max_quantity) {
                throw new AnnotatedQuantityConstraint(_('Quantità superiore al massimo consigliato'), 1);
            }
        }
    }
}
