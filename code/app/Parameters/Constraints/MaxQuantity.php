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
            return __('texts.orders.constraints.relative_max_formatted', [
                'quantity' => sprintf('%.02f', $product->$field),
            ]);
        }

        return null;
    }

    public function hardContraint(): bool
    {
        return false;
    }

    public function test($booked, $quantity)
    {
        $product = $booked->product;

        if ($product->max_quantity != 0) {
            if ($quantity > $product->max_quantity) {
                throw new AnnotatedQuantityConstraint(__('texts.orders.constraints.relative_max'), 1);
            }
        }
    }
}
