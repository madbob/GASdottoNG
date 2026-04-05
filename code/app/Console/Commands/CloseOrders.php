<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use Symfony\Component\Mailer\Exception\HttpTransportException;
use Carbon\Carbon;

use App\Notifications\ClosedOrdersNotification;
use App\Notifications\SupplierOrderShipping;
use App\Printers\Order as OrderPrinter;
use App\Jobs\AggregateSummaries;
use App\Order;

class CloseOrders extends Command
{
    protected $signature = 'close:orders';

    protected $description = 'Chiude automaticamente gli ordini';

    private function closeAndCollect($today)
    {
        $orders = Order::withoutGlobalScopes()->where('status', 'open')->where('end', '<', $today)->get();
        $notifications = [];

        foreach ($orders as $order) {
            try {
                $order->status = 'closed';
                $order->save();

                Log::debug('Chiudo automaticamente ordine per ' . $order->supplier->name);

                foreach ($order->aggregate->gas as $gas) {
                    if (isset($notifications[$gas->id]) === false) {
                        $notifications[$gas->id] = (object) [
                            'gas' => $gas,
                            'orders' => [],
                        ];
                    }

                    $notifications[$gas->id]->orders[] = $order;
                }
            }
            catch (\Exception $e) {
                Log::error('Errore in chiusura automatica ordine: ' . $e->getMessage());
            }
        }

        return $notifications;
    }

    private function notifySuppliers($notifications)
    {
        foreach($notifications as $data) {
            foreach($data->orders as $order) {
                $supplier = $order->supplier;

                if ($supplier->notify_on_close_enabled != 'none') {
                    try {
                        $supplier->notify(new SupplierOrderShipping($data->gas, $order));
                    }
                    catch(HttpTransportException $e) {
                        Log::error('Errore in notifica chiusura ordine a fornitore: ' . print_r($e->getResponse(), true));
                    }
                    catch (\Exception $e) {
                        Log::error('Errore in notifica chiusura ordine a fornitore: ' . $e->getMessage());
                    }
                }
            }
        }
    }

    private function dispatchToReferents($notifiable_users)
    {
        foreach ($notifiable_users as $notifiable) {
            try {
                $notifiable->user->notify(new ClosedOrdersNotification($notifiable->orders, $notifiable->files));
            }
            catch(HttpTransportException $e) {
                Log::error('Errore in notifica chiusura ordine a referente: ' . print_r($e->getResponse(), true));
            }
            catch (\Exception $e) {
                Log::error('Errore in notifica chiusura ordine a referente: ' . $e->getMessage());
            }
        }
    }

    private function notifyReferents($notifications)
    {
        $hub = app()->make('GlobalScopeHub');
        $hub->enable(true);
        $printer = new OrderPrinter();
        $all_files = [];

        foreach($notifications as $data) {
            if ($data->gas->auto_referent_order_summary) {
                $notifiable_users = [];

                $hub->setGas($data->gas->id);

                foreach($data->orders as $order) {
                    $pdf_file_path = $printer->document($order, 'summary', [
                        'format' => 'pdf',
                        'status' => 'pending',
                        'action' => 'save'
                    ]);

                    $csv_file_path = $printer->document($order, 'summary', [
                        'format' => 'csv',
                        'status' => 'pending',
                        'action' => 'save'
                    ]);

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
                }

                $this->dispatchToReferents($notifiable_users);
            }
        }

        foreach($all_files as $file) {
            @unlink($file);
        }
    }

    public function notifyUsers($notifications)
    {
        foreach($notifications as $data) {
            if ($data->gas->auto_user_order_summary) {
                foreach($data->orders as $order) {
                    $aggregate = $order->aggregate;
                    if ($aggregate->last_notify == null && $aggregate->status == 'closed') {
                        AggregateSummaries::dispatch($aggregate->id);
                    }
                }
            }
        }
    }

    public function handle()
    {
        $today = Carbon::today()->format('Y-m-d');
        $notifications = $this->closeAndCollect($today);
        $this->notifySuppliers($notifications);
        $this->notifyReferents($notifications);
        $this->notifyUsers($notifications);
    }
}
