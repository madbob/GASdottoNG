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

            // L'attributo is_pending_package non fa parte del model Product,
            // viene valorizzato staticamente da Order::pendingPackages() ai
            // prodotti per i quali si devono completare le confezioni
            // @phpstan-ignore-next-line
            if ($product->is_pending_package ?? false) {
                return _i('%s Disponibile: %.02f', [
                    sprintf('<span class="badge rounded-pill bg-primary" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-content="%s" data-bs-original-title="" title="">?</span>', _i('Mancano %s %s per completare la confezione per questo ordine', [$still_available, $product->printableMeasure(true)])),
                    $still_available,
                ]);
            }
            else {
                return _i('Disponibile: %.02f (%.02f totale)', [$still_available, $product->max_available]);
            }
        }

        return null;
    }

    public function test($booked, $quantity)
    {
        $product = $booked->product;
        $order = $booked->booking->order;

        if ($product->max_available != 0) {
            if ($quantity > ($this->stillAvailable($product, $order) + $booked->quantity)) {
                throw new InvalidQuantityConstraint(_('Quantità superiore alla disponibilità'), 3);
            }
        }
    }
}
