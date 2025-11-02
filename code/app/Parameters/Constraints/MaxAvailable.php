<?php

namespace App\Parameters\Constraints;

use App;

use App\BookedProduct;
use App\Exceptions\InvalidQuantityConstraint;

class MaxAvailable extends Constraint
{
    public function identifier()
    {
        return 'max_available';
    }

    private function stillAvailable($product, $order)
    {
        if ($product->max_available == 0) {
            return 0;
        }

        $quantity = App::make('GlobalScopeHub')->executedForAll($order->keep_open_packages != 'each', function () use ($product, $order) {
            return BookedProduct::where('product_id', '=', $product->id)->whereHas('booking', function ($query) use ($order) {
                $query->where('order_id', '=', $order->id);
            })->sum('quantity');
        });

        if ($product->portion_quantity != 0) {
            $quantity *= $product->portion_quantity;
        }

        return $product->max_available - $quantity;
    }

    public function printable($product, $order)
    {
        $field = $this->identifier();

        if ($product->$field != 0) {
            $still_available = $this->stillAvailable($product, $order);
            return __('texts.orders.constraints.global_max', [
                'still' => $still_available,
                'global' => $product->max_available,
            ]);
        }

        return null;
    }

    public function test($booked, $quantity)
    {
        $product = $booked->product;
        $order = $booked->booking->order;

        if ($product->max_available != 0 && $quantity > ($this->stillAvailable($product, $order) + $booked->quantity)) {
            throw new InvalidQuantityConstraint(__('texts.orders.constraints.global_max_generic'), 3);
        }
    }
}
