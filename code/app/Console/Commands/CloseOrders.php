<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Notifications\ClosedOrderNotification;

use Log;

use App\Order;
use App\Role;

class CloseOrders extends Command
{
    protected $signature = 'check:orders';
    protected $description = 'Controlla lo stato degli ordini, ed eventualmente li chiude';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $orders = Order::where('status', 'open')->where('end', '<', date('Y-m-d'))->get();

        foreach($orders as $order) {
            try {
                $order->status = 'closed';
                $order->save();

                $users = Role::everybodyCan('supplier.orders', $order->supplier);
                foreach($users as $u) {
                    $u->notify(new ClosedOrderNotification($order));
                }
            }
            catch(\Exception $e) {
                Log::error('Errore in chiusura automatica ordine: ' . $e->getMessage());
            }
        }
    }
}
