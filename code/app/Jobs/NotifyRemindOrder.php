<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Notifications\RemindOrderNotification;
use App\Order;

class NotifyRemindOrder implements ShouldQueue
{
	use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $orders_id;

    public function __construct($orders_id)
    {
        $this->orders_id = $orders_id;
    }

    public function handle()
    {
		$hub = app()->make('GlobalScopeHub');
		$gas = $hub->getGasObj();

		$aggregate_users = [];

		foreach($this->orders_id as $order_id) {
	        $order = Order::find($order_id);
			if (is_null($order)) {
				\Log::error('Ordine non trovato per notifica reminder: ' . $order_id);
				continue;
			}

	        $users = $order->notifiableUsers($gas);

			foreach($users as $user) {
				if (isset($aggregate_users[$user->id]) == false) {
					$aggregate_users[$user->id] = (object) [
						'user' => $user,
						'orders' => [],
					];
				}

				$aggregate_users[$user->id]->orders[] = $order;
			}
		}

		foreach($aggregate_users as $auser) {
			try {
				$auser->user->notify(new RemindOrderNotification($auser->orders));
			}
			catch(\Exception $e) {
				\Log::error('Impossibile inoltrare mail di promemoria ordine: ' . $e->getMessage());
			}
		}
    }
}
