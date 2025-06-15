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
            return __('texts.orders.constraints.relative_min_formatted', [
                'quantity' => sprintf('%.02f', $product->$field),
            ]);
        }

        return null;
    }

    public function test($booked, $quantity)
    {
        $product = $booked->product;

        if ($product->min_quantity != 0) {
            if ($quantity < $product->min_quantity) {
                throw new InvalidQuantityConstraint(__('texts.orders.constraints.relative_min'), 1);
            }
        }
    }
}
