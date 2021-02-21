<?php

/*
    Per invocare una funzione asincrona, usare async_job().
    La richiesta viene internamente serializzata ed invocata verso questo
    Controller senza attenderne la risposta, in modo che l'utente possa
    procedere senza aspettare
*/

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Log;
use App;

use App\Notifications\NewOrderNotification;
use App\Notifications\ClosedOrderNotification;
use App\Notifications\SupplierOrderShipping;
use App\Notifications\BookingNotification;

use App\User;
use App\Role;
use App\Aggregate;
use App\Order;

class JobsController extends Controller
{
    private $hub;

    public function execute(Request $request)
    {
        $auth_key = $request->input('auth_key');
        if ($auth_key != substr(env('APP_KEY'), -5)) {
            Log::debug('Accesso negato ad esecuzione job');
            abort(503);
        }

        $this->hub = App::make('GlobalScopeHub');
        $this->hub->setGas($request->input('gas_id'));

        $action = $request->input('action');

        switch($action) {
            case 'order_open':
                $order_id = $request->input('order_id');
                $order = Order::find($order_id);
                $this->sendOrderNotificationMail($order);
                break;

            case 'order_close':
                $order_id = $request->input('order_id');
                $order = Order::find($order_id);
                $this->sendOrderClosingMails($order);
                break;

            case 'aggregate_summary':
                $aggregate_id = $request->input('aggregate_id');
                $message = $request->input('message');
                $aggregate = Aggregate::find($aggregate_id);
                $this->sendAggregateSummaryMails($aggregate, $message);
                break;
        }
    }

    private function sendOrderNotificationMail($order)
    {
        if (is_null($order->first_notify) == false) {
            return;
        }

        foreach($order->aggregate->gas as $gas) {
            $this->hub->setGas($gas->id);

            if ($gas->getConfig('notify_all_new_orders')) {
                $query_users = User::whereNull('parent_id');
            }
            else {
                $query_users = User::whereHas('suppliers', function($query) use ($order) {
                    $query->where('suppliers.id', $order->supplier->id);
                });
            }

            $deliveries = $order->deliveries;
            if ($deliveries->isEmpty() == false) {
                $query_users->whereIn('preferred_delivery_id', $deliveries->pluck('id'));
            }

            $users = $query_users->get();

            foreach($users as $user) {
                try {
                    $user->notify(new NewOrderNotification($order));
                }
                catch(\Exception $e) {
                    Log::error('Impossibile inoltrare mail di notifica apertura ordine: ' . $e->getMessage());
                }
            }
        }

        $order->first_notify = date('Y-m-d');
        $order->save();
    }

    private function sendOrderClosingMails($order)
    {
        $aggregate = $order->aggregate;

        foreach($aggregate->gas as $gas) {
            $this->hub->setGas($gas->id);

            $pdf_file_path = $order->document('summary', 'pdf', 'save', null, 'booked', null);
            $csv_file_path = $order->document('summary', 'csv', 'save', null, 'booked', null);

            $referents = Role::everybodyCan('supplier.orders', $order->supplier);
            foreach($referents as $u) {
                $u->notify(new ClosedOrderNotification($order, $pdf_file_path, $csv_file_path));
            }

            @unlink($pdf_file_path);
            @unlink($csv_file_path);
        }

        if ($order->isRunning() == false) {
            foreach($aggregate->gas as $gas) {
                if ($gas->auto_supplier_order_summary) {
                    $this->hub->enable(false);

                    $pdf_file_path = $order->document('summary', 'pdf', 'save', null, 'booked', null);
                    $csv_file_path = $order->document('summary', 'csv', 'save', null, 'booked', null);

                    $order->supplier->notify(new SupplierOrderShipping($order, $pdf_file_path, $csv_file_path));

                    @unlink($pdf_file_path);
                    @unlink($csv_file_path);

                    $this->hub->enable(true);
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

    private function sendAggregateSummaryMails($aggregate, $message)
    {
        $this->hub->enable(false);

        $date = date('Y-m-d');
        foreach($aggregate->orders as $order) {
            $order->last_notify = $date;
            $order->save();
        }

        if ($aggregate->isActive()) {
            $status = ['pending', 'saved'];
        }
        else {
            $status = ['shipped'];
        }

        foreach($aggregate->bookings as $booking) {
            if (in_array($booking->status, $status)) {
                try {
                    $booking->user->notify(new BookingNotification($booking, $message));
                }
                catch(\Exception $e) {
                    Log::error('Impossibile inviare notifica mail prenotazione di ' . $booking->user->id);
                }
            }
        }

        $this->hub->enable(true);
    }
}
