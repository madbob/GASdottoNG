<?php

namespace App\Jobs;

use Log;

use App\Notifications\ClosedOrderNotification;
use App\Notifications\SupplierOrderShipping;

use App\User;
use App\Order;

class NotifyClosedOrder extends Job
{
    public $order_id;

    public function __construct($order_id)
    {
        parent::__construct();
        $this->order_id = $order_id;
    }

    private function dispatchToSupplier($order)
    {
        if ($order->isRunning() == false) {
            foreach($order->aggregate->gas as $gas) {
                if ($gas->auto_supplier_order_summary) {
                    try {
                        $this->hub->enable(false);

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
        $order = Order::find($this->order_id);
        $aggregate = $order->aggregate;
        $closed_aggregate = ($aggregate->last_notify == null && $aggregate->status == 'closed');

        foreach($aggregate->gas as $gas) {
            $this->hub->setGas($gas->id);

            $pdf_file_path = $order->document('summary', 'pdf', 'save', null, 'booked', null);
            $csv_file_path = $order->document('summary', 'csv', 'save', null, 'booked', null);

            $referents = everybodyCan('supplier.orders', $order->supplier);
            foreach($referents as $u) {
                try {
                    $u->notify(new ClosedOrderNotification($order, $pdf_file_path, $csv_file_path));
                }
                catch(\Exception $e) {
                    Log::error('Errore in notifica chiusura ordine: ' . $e->getMessage());
                }
            }

            DeleteFiles::dispatch([$pdf_file_path, $csv_file_path])->delay(now()->addMinutes($referents->count()));

            if ($closed_aggregate && $gas->auto_user_order_summary) {
                AggregateSummaries::dispatch($aggregate->id);
            }
        }

        $this->dispatchToSupplier($order);

        /*
            Nota bene: le valutazioni sull'invio automatico della mail di
            riepilogo agli utente avviene in CloseOrders, che considera tutti
            gli ordini chiusi nello stesso momento e contempla i relativi
            Aggregate
        */
    }
}
