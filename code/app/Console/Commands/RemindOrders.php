<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use Carbon\Carbon;

use App\Notifications\RemindOrderNotification;
use App\Gas;
use App\Order;

class RemindOrders extends Command
{
    protected $signature = 'remind:orders';

    protected $description = 'Invia le notifiche di promemoria per gli ordini';

    private function deliverMails($aggregate_users)
    {
        Log::info('Invio notifica chiusura ordini a ' . count($aggregate_users) . ' utenti');
        foreach ($aggregate_users as $auser) {
            try {
                if (filled($auser->user->email)) {
                    $auser->user->notify(new RemindOrderNotification($auser->orders));
                }
            }
            catch (\Exception $e) {
                \Log::error('Impossibile inoltrare mail di promemoria ordine a ' . $auser->user->email . ': ' . $e->getMessage());
            }
        }
    }

    private function aggregateNotifications($notifications, $today_formatted)
    {
        $hub = app()->make('GlobalScopeHub');

        foreach ($notifications as $gas_id => $data) {
            Log::info('Invio promemoria per ordini ' . join(', ', array_map(fn($o) => $o->id, $data->orders)));
            $hub->setGas($data->gas->id);

            foreach ($data->orders as $order) {
                foreach ($order->aggregate->gas as $gas) {
                    $aggregate_users = [];
                    $users = $order->notifiableUsers($gas);

                    foreach ($users as $user) {
                        if (isset($aggregate_users[$user->id]) === false) {
                            $aggregate_users[$user->id] = (object) [
                                'user' => $user,
                                'orders' => [],
                            ];
                        }

                        $aggregate_users[$user->id]->orders[] = $order;
                    }

                    $this->deliverMails($aggregate_users);
                }
            }

            $data->gas->setConfig('last_sent_order_reminder', $today_formatted);
        }
    }

    private function filterNotifiable($today, $today_formatted)
    {
        $today_formatted = $today->format('Y-m-d');
        $orders = Order::where('status', 'open')->where('end', '>', $today)->get();
        $notifications = [];

        foreach ($orders as $order) {
            foreach ($order->aggregate->gas as $gas) {
                if ($gas->hasFeature('send_order_reminder') === false) {
                    continue;
                }

                if ($gas->last_sent_order_reminder == $today_formatted) {
                    continue;
                }

                $days = (int) $gas->send_order_reminder;
                $expiration = $today->copy()->addDays($days);

                if ($order->end->format('Y-m-d') == $expiration->format('Y-m-d')) {
                    if (isset($notifications[$gas->id]) === false) {
                        $notifications[$gas->id] = (object) [
                            'gas' => $gas,
                            'orders' => [],
                        ];
                    }

                    $notifications[$gas->id]->orders[] = $order;
                }
            }
        }

        return $notifications;
    }

    public function handle()
    {
        $today = Carbon::today();
        $today_formatted = $today->format('Y-m-d');
        $notifications = $this->filterNotifiable($today, $today_formatted);
        $this->aggregateNotifications($notifications, $today_formatted);
    }
}
