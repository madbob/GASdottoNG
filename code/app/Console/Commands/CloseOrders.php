<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Log;

use App\Order;

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
        $aggregates = [];

        foreach($orders as $order) {
            try {
                $aggregates[$order->aggregate->id] = $order->aggregate;
                $order->status = 'closed';
                $order->save();
            }
            catch(\Exception $e) {
                Log::error('Errore in chiusura automatica ordine: ' . $e->getMessage());
            }
        }

        foreach($aggregates as $aggregate) {
            $aggregate->refresh();

            if ($aggregate->last_notify == null && $aggregate->status == 'closed') {
                foreach($aggregate->gas as $gas) {
                    if ($gas->auto_user_order_summary) {
                        async_job('aggregate_summary', ['aggregate_id' => $aggregate->id, 'message' => '']);
                    }
                }
            }
        }
    }
}
