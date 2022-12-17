<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Log;

use App\Jobs\NotifyClosedOrder;
use App\Order;

class CloseOrders extends Command
{
    protected $signature = 'close:orders';
    protected $description = 'Controlla lo stato degli ordini, ed eventualmente li chiude';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $orders = Order::where('status', 'open')->where('end', '<', date('Y-m-d'))->get();
        $closed = [];

        foreach($orders as $order) {
            try {
                echo "chiudo " . $order->id . "\n";
                $order->status = 'closed';
                $order->save();
                $closed[] = $order->id;
            }
            catch(\Exception $e) {
                Log::error('Errore in chiusura automatica ordine: ' . $e->getMessage());
            }
        }

        NotifyClosedOrder::dispatch($closed);
    }
}
