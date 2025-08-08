<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Symfony\Component\Mailer\Exception\HttpTransportException;

use App\Notifications\ClosedOrdersNotification;
use App\Notifications\SupplierOrderShipping;
use App\Printers\Order as OrderPrinter;
use App\Order;

class NotifyClosedOrder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $orders;

    public function __construct($orders)
    {
        $this->orders = $orders;
    }

    private function filesForSupplier($order)
    {
        $printer = new OrderPrinter();

        $type = $order->supplier->notify_on_close_enabled;
        if ($type == 'shipping_summary') {
            $types = ['shipping', 'summary'];
        }
        else {
            $types = [$type];
        }

        $files = [];
        foreach ($types as $type) {
            foreach (['pdf', 'csv'] as $format) {
                $f = $printer->document($order, $type, ['format' => $format, 'status' => 'pending', 'extra_modifiers' => 0, 'action' => 'save']);
                if ($f) {
                    $files[] = $f;
                }
            }
        }

        return $files;
    }

    private function dispatchToSupplier($order)
    {
        if ($order->isRunning() === false) {
            $supplier = $order->supplier;

            if ($supplier->notify_on_close_enabled != 'none') {
                $hub = app()->make('GlobalScopeHub');

                foreach ($order->aggregate->gas as $gas) {
                    try {
                        $hub->enable(false);
                        $files = $this->filesForSupplier($order);

                        /*
                            Reminder: i files vengono automaticamente rimossi
                            dopo l'invio della notifica, da parte di
                            SupplierOrderShipping
                        */
                        $supplier->notify(new SupplierOrderShipping($gas, $order, $files));

                        $hub->enable(true);
                    }
                    catch(HttpTransportException $e) {
                        \Log::error('Errore in notifica chiusura ordine a fornitore: ' . print_r($e->getResponse(), true));
                    }
                    catch (\Exception $e) {
                        \Log::error('Errore in notifica chiusura ordine a fornitore: ' . $e->getMessage());
                    }

                    break;
                }
            }
        }
    }

    private function dispatchToReferents($notifiable_users)
    {
        foreach ($notifiable_users as $notifiable) {
            if ($notifiable->user->gas->auto_referent_order_summary) {
                try {
                    $notifiable->user->notify(new ClosedOrdersNotification($notifiable->orders, $notifiable->files));
                    sleep(1);
                }
                catch(HttpTransportException $e) {
                    \Log::error('Errore in notifica chiusura ordine: ' . print_r($e->getResponse(), true));
                }
                catch (\Exception $e) {
                    \Log::error('Errore in notifica chiusura ordine: ' . $e->getMessage());
                }
            }
        }
    }

    public function handle()
    {
        $printer = new OrderPrinter();
        $notifiable_users = [];
        $all_files = [];
        $aggregates = [];

        $hub = app()->make('GlobalScopeHub');
        $hub->enable(false);

        foreach ($this->orders as $order_id) {
            $order = Order::find($order_id);
            if (is_null($order)) {
                \Log::error('Non trovato ordine in fase di notifica chiusura: ' . $order_id . ' / ' . env('DB_DATABASE'));

                continue;
            }

            $aggregate = $order->aggregate;
            $closed_aggregate = ($aggregate->last_notify == null && $aggregate->status == 'closed');

            foreach ($aggregate->gas as $gas) {
                $hub->enable(true);
                $hub->setGas($gas->id);

                $pdf_file_path = $printer->document($order, 'summary', ['format' => 'pdf', 'status' => 'pending', 'action' => 'save']);
                $csv_file_path = $printer->document($order, 'summary', ['format' => 'csv', 'status' => 'pending', 'action' => 'save']);

                $all_files[] = $pdf_file_path;
                $all_files[] = $csv_file_path;

                $referents = everybodyCan('supplier.orders', $order->supplier);
                foreach ($referents as $u) {
                    if (isset($notifiable_users[$u->id]) === false) {
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

        foreach ($aggregates as $aggregate) {
            AggregateSummaries::dispatch($aggregate);
        }

        $this->dispatchToReferents($notifiable_users);
        DeleteFiles::dispatch($all_files)->delay(now()->addMinutes(count($notifiable_users)));
    }
}
