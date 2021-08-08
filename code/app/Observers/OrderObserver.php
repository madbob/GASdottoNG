<?php

namespace App\Observers;

use App\Jobs\NotifyNewOrder;
use App\Jobs\NotifyClosedOrder;

use App\Order;

class OrderObserver
{
    public function created(Order $order)
    {
        if ($order->status == 'open') {
            NotifyNewOrder::dispatch($order->id);
        }
    }

    public function updated(Order $order)
    {
        if ($order->wasChanged('status')) {
            if ($order->status == 'open') {
                NotifyNewOrder::dispatch($order->id);
            }
            else if ($order->status == 'closed') {
                NotifyClosedOrder::dispatch($order->id);
            }
        }
    }

    public function deleted(Order $order)
    {
        $order->modifiers()->delete();
    }
}
