<?php

namespace App\Jobs;

use Log;

use App\Notifications\RemindOrderNotification;
use App\Gas;
use App\Order;

class NotifyRemindOrder extends Job
{
	public $gas_id;
    public $orders_id;

    public function __construct($gas_id, $orders_id)
    {
        parent::__construct();
		$this->gas_id = $gas_id;
        $this->orders_id = $orders_id;
    }

    protected function realHandle()
    {
		$gas = Gas::find($this->gas_id);
		if ($gas->hasFeature('send_order_reminder') == false) {
			return;
		}

		$aggregate_users = [];

		foreach($this->orders_id as $order_id) {
	        $order = Order::find($order_id);

	        $this->hub->setGas($gas->id);
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
				Log::error('Impossibile inoltrare mail di promemoria ordine: ' . $e->getMessage());
			}
		}
    }
}
