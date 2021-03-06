<?php

namespace App\Jobs;

use Log;

use App\Notifications\NewOrderNotification;

use App\User;
use App\Order;

class NotifyNewOrder extends Job
{
    public $order_id;

    public function __construct($order_id)
    {
        parent::__construct();
        $this->order_id = $order_id;
    }

    protected function realHandle()
    {
        $order = Order::find($this->order_id);

        if (is_null($order->first_notify) == false) {
            return;
        }

        foreach($order->aggregate->gas as $gas) {
            $this->hub->setGas($gas->id);

            if ($gas->getConfig('notify_all_new_orders')) {
                $query_users = User::whereNull('parent_id');
            }
            else {
                $query_users = User::whereHas('suppliers', function($query) use ($order) {
                    $query->where('suppliers.id', $order->supplier->id);
                });
            }

            $deliveries = $order->deliveries;
            if ($deliveries->isEmpty() == false) {
                $query_users->whereIn('preferred_delivery_id', $deliveries->pluck('id'));
            }

            $users = $query_users->get();

            foreach($users as $user) {
                try {
                    $user->notify(new NewOrderNotification($order));
                }
                catch(\Exception $e) {
                    Log::error('Impossibile inoltrare mail di notifica apertura ordine: ' . $e->getMessage());
                }
            }
        }

        $order->first_notify = date('Y-m-d');
        $order->save();
    }
}
