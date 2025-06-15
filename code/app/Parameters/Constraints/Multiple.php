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

        if ($product->$field > 1) {
            return __('texts.orders.constraints.relative_multiple_formatted', [
                'quantity' => sprintf('%.02f', $product->$field),
            ]);
        }

        return null;
    }

    public function test($booked, $quantity)
    {
        $product = $booked->product;

        if ($product->multiple != 0) {
            if (fmod($quantity, $product->multiple) != 0) {
                throw new InvalidQuantityConstraint(__('texts.orders.constraints.relative_multiple'), 2);
            }
        }
    }
}
