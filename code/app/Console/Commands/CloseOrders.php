<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;

use App\Jobs\NotifyClosedOrder;
use App\Order;

class CloseOrders extends Command
{
    protected $signature = 'close:orders';
    protected $description = 'Controlla lo stato degli ordini, ed eventualmente li chiude';

    public function handle()
    {
        $today = Carbon::today()->format('Y-m-d');
        $orders = Order::withoutGlobalScopes()->where('status', 'open')->where('end', '<', $today)->get();
        $closed = [];

        foreach($orders as $order) {
            try {
                $order->status = 'closed';
                $order->save();
                $closed[] = $order->id;
            }
            catch(\Exception $e) {
                \Log::error('Errore in chiusura automatica ordine: ' . $e->getMessage());
            }
        }

        if (empty($closed) == false) {
            NotifyClosedOrder::dispatch($closed);
        }
    }
}
