<?php

namespace App\Jobs;

use Log;

use App\Notifications\RemindOrderNotification;
use App\Gas;
use App\Order;

class NotifyRemindOrder extends Job
{
	public $gas_id;
    public $order_id;

    public function __construct($gas_id, $order_id)
    {
        parent::__construct();
		$this->gas_id = $gas_id;
        $this->order_id = $order_id;
    }

    protected function realHandle()
    {
		$gas = Gas::find($this->gas_id);
        $order = Order::find($this->order_id);

		if ($gas->hasFeature('send_order_reminder') == false) {
			return;
		}

        $this->hub->setGas($gas->id);
        $users = $order->notifiableUsers($gas);

        foreach($users as $user) {
            try {
                $user->notify(new RemindOrderNotification($order));
            }
            catch(\Exception $e) {
                Log::error('Impossibile inoltrare mail di promemoria ordine: ' . $e->getMessage());
            }
        }
    }
}
