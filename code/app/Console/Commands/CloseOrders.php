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
        $notifications = [];

        foreach ($orders as $order) {
            try {
                $order->status = 'closed';
                $order->save();

                \Log::debug('Chiudo automaticamente ordine per ' . $order->supplier->name);

                foreach ($order->aggregate->gas as $gas) {
                    if (isset($notifications[$gas->id]) === false) {
                        $notifications[$gas->id] = [];
                    }

                    $notifications[$gas->id][] = $order->id;
                }
            }
            catch (\Exception $e) {
                \Log::error('Errore in chiusura automatica ordine: ' . $e->getMessage());
            }
        }

        $hub = app()->make('GlobalScopeHub');

        foreach ($notifications as $gas_id => $orders) {
            $hub->setGas($gas_id);
            NotifyClosedOrder::dispatch($orders);
        }
    }
}
