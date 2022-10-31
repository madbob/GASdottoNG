<?php

namespace App\Jobs;

use Log;

use App\Notifications\NewOrderNotification;
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

        $order->first_notify = date('Y-m-d');
        $order->save();

        foreach($order->aggregate->gas as $gas) {
            $this->hub->setGas($gas->id);
            $users = $order->notifiableUsers($gas);

            foreach($users as $user) {
                try {
                    $user->notify(new NewOrderNotification($order));
                }
                catch(\Exception $e) {
                    Log::error('Impossibile inoltrare mail di notifica apertura ordine: ' . $e->getMessage());
                }
            }
        }
    }
}
