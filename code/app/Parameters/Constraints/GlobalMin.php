<?php

namespace App\Parameters\Constraints;

use App;

use App\BookedProduct;

class GlobalMin extends Constraint
{
    public function identifier()
    {
        return 'global_min';
    }

    private function stillAvailable($product, $order)
    {
        if ($product->global_min == 0) {
            return 0;
        }

        $quantity = App::make('GlobalScopeHub')->executedForAll(false, function () use ($product, $order) {
            return BookedProduct::where('product_id', '=', $product->id)->whereHas('booking', function ($query) use ($order) {
                $query->where('order_id', '=', $order->id);
            })->sum('quantity');
        });

        if ($product->portion_quantity != 0) {
            $quantity *= $product->portion_quantity;
        }

        return $product->global_min - $quantity;
    }

    public function printable($product, $order)
    {
        $field = $this->identifier();

        if ($product->$field != 0) {
            $still_available = $this->stillAvailable($product, $order);
            if ($still_available > 0) {
                return _i('Minimo Complessivo: %.02f (%.02f totale)', [$still_available, $product->global_min]);
            }
        }

        return null;
    }

    public function test($booked, $quantity)
    {
        // dummy
    }
}
