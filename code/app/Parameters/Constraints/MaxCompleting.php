<?php

/*
    Questo constraint verifica gli ordini in fase di completamento confezioni,
    ed impedisce di sforare i limiti (ovvero: che un utente prenoti una quantità
    tale da completare la confezione precedente, ma lasciarne incompleta
    un'altra)
*/

namespace App\Parameters\Constraints;

use App\Exceptions\InvalidQuantityConstraint;

class MaxCompleting extends Constraint
{
    public function identifier()
    {
        return 'max_completing';
    }

    private function maxToComplete($product, $order)
    {
        if ($order->inPendingPackageState() == false) {
            return false;
        }

        $pending = $order->pendingPackages();
        foreach($pending as $p) {
            if ($p->id == $product->id) {
                if ($p->max_completing != 0) {
                    return [$p->max_completing, $p->quantity_completing];
                }
            }
        }

        return false;
    }

    public function printable($product, $order)
    {
        $max = $this->maxToComplete($product, $order);

        if ($max !== false) {
            $diff = $max[0] - $max[1];

            $text = __('texts.orders.constraints.completing_max_formatted', [
                'still' => $diff,
                'measure' => $product->printableMeasure(true)
            ]);

            return __('texts.orders.constraints.completing_max_short', [
                'icon' => sprintf('<span class="badge rounded-pill bg-primary" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-content="%s" data-bs-original-title="" title="">?</span>', $text),
                'quantity' => sprintf('%.02f', $diff),
            ]);
        }

        return null;
    }

    public function test($booked, $quantity)
    {
        $product = $booked->product;
        $order = $booked->booking->order;
        $max = $this->maxToComplete($product, $order);

        if ($max !== false) {
            $diff = $max[0] - $max[1] - $booked->quantity;
            if ($diff <= 0) {
                $diff = $max[0];
            }

            if ($quantity > $diff) {
                throw new InvalidQuantityConstraint(__('texts.orders.constraints.completing_max'), 1);
            }
        }
    }
}
