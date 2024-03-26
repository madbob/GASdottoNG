<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Notifications\NewOrderNotification;
use App\Order;

class NotifyNewOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $order_id;

    public function __construct($order_id)
    {
        $this->order_id = $order_id;
    }

    public function handle()
    {
        $order = Order::find($this->order_id);

        if (is_null($order) || is_null($order->first_notify) == false) {
            return;
        }

        $order->first_notify = date('Y-m-d');
        $order->save();
        $hub = app()->make('GlobalScopeHub');

        foreach($order->aggregate->gas as $gas) {
            $hub->setGas($gas->id);
            $users = $order->notifiableUsers($gas);

            foreach($users as $user) {
                try {
                    $user->notify(new NewOrderNotification($order));
                }
                catch(\Exception $e) {
                    \Log::error('Impossibile inoltrare mail di notifica apertura ordine: ' . $e->getMessage());
                }
            }
        }
    }
}
