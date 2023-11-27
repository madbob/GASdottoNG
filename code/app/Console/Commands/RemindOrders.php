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
        $orders = Order::where('status', 'open')->where('end', '>', Carbon::today()->format('Y-m-d'))->get();
        $notifications = [];

		foreach($orders as $order) {
			foreach($order->aggregate->gas as $gas) {
				if ($gas->hasFeature('send_order_reminder') == false) {
					continue;
				}

				$today = Carbon::today();

				if ($gas->last_sent_order_reminder == $today->format('Y-m-d')) {
					continue;
				}

				$days = $gas->send_order_reminder;
				$expiration = $today->addDays($days);

				if ($order->end == $expiration->format('Y-m-d')) {
                    if (isset($notifications[$gas->id]) == false) {
                        $notifications[$gas->id] = [];
                    }

                    $notifications[$gas->id][] = $order->id;
				}
			}
		}

        foreach($notifications as $gas_id => $orders) {
            Log::info('Invio promemoria per ordini ' . join(', ', $orders));
            NotifyRemindOrder::dispatch($gas_id, $orders);
        }

		$gas = Gas::all();
		$today = Carbon::today()->format('Y-m-d');

		foreach($gas as $g) {
			$g->setConfig('last_sent_order_reminder', $today);
		}
    }
}
