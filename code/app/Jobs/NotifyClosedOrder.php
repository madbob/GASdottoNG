<?php

namespace App\Jobs;

use Log;

use App\Notifications\ClosedOrderNotification;
use App\Notifications\SupplierOrderShipping;
use App\Jobs\AggregateSummaries;

use App\User;
use App\Order;
use App\Role;

class NotifyClosedOrder extends Job
{
    public $order_id;

    public function __construct($order_id)
    {
        parent::__construct();
        $this->order_id = $order_id;
    }

    protected function realHandle()
    {
        $order = Order::find($this->order_id);
        $aggregate = $order->aggregate;
        $closed_aggregate = ($aggregate->last_notify == null && $aggregate->status == 'closed');

        foreach($aggregate->gas as $gas) {
            $this->hub->setGas($gas->id);

            $pdf_file_path = $order->document('summary', 'pdf', 'save', null, 'booked', null);
            $csv_file_path = $order->document('summary', 'csv', 'save', null, 'booked', null);

            $referents = Role::everybodyCan('supplier.orders', $order->supplier);
            foreach($referents as $u) {
                try {
                    $u->notify(new ClosedOrderNotification($order, $pdf_file_path, $csv_file_path));
                }
                catch(\Exception $e) {
                    Log::error('Errore in notifica chiusura ordine: ' . $e->getMessage());
                }
            }

            @unlink($pdf_file_path);
            @unlink($csv_file_path);

            if ($closed_aggregate && $gas->auto_user_order_summary) {
                AggregateSummaries::dispatch($aggregate->id);
            }
        }

        if ($order->isRunning() == false) {
            foreach($aggregate->gas as $gas) {
                if ($gas->auto_supplier_order_summary) {
                    try {
                        $this->hub->enable(false);

                        $pdf_file_path = $order->document('summary', 'pdf', 'save', null, 'booked', null);
                        $csv_file_path = $order->document('summary', 'csv', 'save', null, 'booked', null);

                        $order->supplier->notify(new SupplierOrderShipping($order, $pdf_file_path, $csv_file_path));

                        @unlink($pdf_file_path);
                        @unlink($csv_file_path);

                        $this->hub->enable(true);
                    }
                    catch(\Exception $e) {
                        Log::error('Errore in notifica chiusura ordine a fornitore: ' . $e->getMessage());
                    }

                    break;
                }
            }
        }

        /*
            Nota bene: le valutazioni sull'invio automatico della mail di
            riepilogo agli utente avviene in CloseOrders, che considera tutti
            gli ordini chiusi nello stesso momento e contempla i relativi
            Aggregate
        */
    }
}
