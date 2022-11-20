<?php

namespace App\Jobs;

use Log;

use App\Notifications\ClosedOrdersNotification;
use App\Notifications\SupplierOrderShipping;

use App\User;
use App\Order;

class NotifyClosedOrder extends Job
{
    public $orders;

    public function __construct($orders)
    {
        parent::__construct();
        $this->orders = $orders;
    }

    private function dispatchToSupplier($order)
    {
        if ($order->isRunning() == false) {
            foreach($order->aggregate->gas as $gas) {
                if ($gas->auto_supplier_order_summary) {
                    try {
                        $this->hub->enable(false);

                        /*
                            I files vengono già rimossi dopo l'invio della
                            notifica al fornitore
                        */
                        $pdf_file_path = $order->document('summary', 'pdf', 'save', null, 'booked', null);
                        $csv_file_path = $order->document('summary', 'csv', 'save', null, 'booked', null);
                        $order->supplier->notify(new SupplierOrderShipping($order, $pdf_file_path, $csv_file_path));

                        $this->hub->enable(true);
                    }
                    catch(\Exception $e) {
                        Log::error('Errore in notifica chiusura ordine a fornitore: ' . $e->getMessage());
                    }

                    break;
                }
            }
        }
    }

    protected function realHandle()
    {
        $notifiable_users = [];
        $all_files = [];
        $aggregates = [];

        foreach($this->orders as $order_id) {
            $order = Order::find($order_id);
            $aggregate = $order->aggregate;
            $closed_aggregate = ($aggregate->last_notify == null && $aggregate->status == 'closed');

            foreach($aggregate->gas as $gas) {
                $this->hub->setGas($gas->id);

                $pdf_file_path = $order->document('summary', 'pdf', 'save', null, 'booked', null);
                $csv_file_path = $order->document('summary', 'csv', 'save', null, 'booked', null);

                $all_files[] = $pdf_file_path;
                $all_files[] = $csv_file_path;

                $referents = everybodyCan('supplier.orders', $order->supplier);
                foreach($referents as $u) {
                    if (isset($notifiable_users[$u->id]) == false) {
                        $notifiable_users[$u->id] = (object) [
                            'user' => $u,
                            'orders' => [],
                            'files' => [],
                        ];
                    }

                    $notifiable_users[$u->id]->orders[] = $order;
                    $notifiable_users[$u->id]->files[] = $pdf_file_path;
                    $notifiable_users[$u->id]->files[] = $csv_file_path;
                }

                if ($closed_aggregate && $gas->auto_user_order_summary) {
                    $aggregates[$aggregate->id] = $aggregate->id;
                }
            }

            $this->dispatchToSupplier($order);
        }

        foreach($aggregates as $aggregate) {
            AggregateSummaries::dispatch($aggregate);
        }

        foreach($notifiable_users as $notifiable) {
            try {
                $notifiable->user->notify(new ClosedOrdersNotification($notifiable->orders, $notifiable->files));
            }
            catch(\Exception $e) {
                Log::error('Errore in notifica chiusura ordine: ' . $e->getMessage());
            }
        }

        DeleteFiles::dispatch($all_files)->delay(now()->addMinutes(count($notifiable_users)));
    }
}
