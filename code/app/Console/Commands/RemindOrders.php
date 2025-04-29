<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use Carbon\Carbon;

use App\Jobs\NotifyRemindOrder;
use App\Gas;
use App\Order;

class RemindOrders extends Command
{
    protected $signature = 'remind:orders';

    protected $description = 'Invia le notifiche di promemoria per gli ordini';

    public function handle()
    {
        $today = Carbon::today();
        $today_formatted = $today->format('Y-m-d');
        $orders = Order::where('status', 'open')->where('end', '>', $today)->get();
        $notifications = [];

        foreach ($orders as $order) {
            foreach ($order->aggregate->gas as $gas) {
                if ($gas->hasFeature('send_order_reminder') == false) {
                    continue;
                }

                if ($gas->last_sent_order_reminder == $today_formatted) {
                    continue;
                }

                $days = (int) $gas->send_order_reminder;
                $expiration = $today->copy()->addDays($days);

                if ($order->end == $expiration->format('Y-m-d')) {
                    if (isset($notifications[$gas->id]) == false) {
                        $notifications[$gas->id] = [];
                    }

                    $notifications[$gas->id][] = $order->id;
                }
            }
        }

        $hub = app()->make('GlobalScopeHub');

        foreach ($notifications as $gas_id => $orders) {
            Log::info('Invio promemoria per ordini ' . implode(', ', $orders));
            $hub->setGas($gas_id);
            NotifyRemindOrder::dispatch($orders);
        }

        $gas = Gas::all();
        $today = Carbon::today()->format('Y-m-d');

        foreach ($gas as $g) {
            $g->setConfig('last_sent_order_reminder', $today);
        }
    }
}
