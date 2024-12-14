<?php

namespace App\Observers;

use App\Jobs\NotifyNewOrder;

use Log;

use App\Aggregate;
use App\Order;
use App\Date;

class OrderObserver
{
    private function resetOlderDates($order)
    {
        $last_date = $order->shipping ? $order->shipping : $order->end;
        Date::where('target_type', 'App\Supplier')->where('target_id', $order->supplier_id)->where('recurring', '')->where('date', '<=', $last_date)->delete();

        $recurrings = Date::where('target_type', 'App\Supplier')->where('target_id', $order->supplier_id)->where('recurring', '!=', '')->get();
        foreach ($recurrings as $d) {
            $d->updateRecurringToDate($last_date);
        }
    }

    private function attachModifiers($order)
    {
        foreach ($order->supplier->modifiers as $mod) {
            if ($mod->active || $mod->always_on == true) {
                $new_mod = $mod->replicate();
                $new_mod->target_id = $order->id;
                $new_mod->target_type = get_class($order);
                $new_mod->save();
            }
        }
    }

    private function dispatchNotifications($order)
    {
        if ($order->status == 'open') {
            /*
                Nota bene: questo funziona solo in virtù del fatto che i job
                asincroni vengono eseguiti in differita. Infatti se l'ordine
                viene abilitato solo per alcuni luoghi di consegna questi
                vengono associati solo dopo la creazione dell'Order sul
                database, dunque solo dopo l'esecuzione di questa funzione si
                conosce l'elenco degli utenti che sono davvero da notificare
            */
            try {
                NotifyNewOrder::dispatch($order->id);
            }
            catch (\Exception $e) {
                Log::error('Unable to trigger NotifyNewOrder job: ' . $e->getMessage());
            }
        }
    }

    public function created(Order $order)
    {
        $supplier = $order->supplier;

        /*
            Aggancio i prodotti attualmente prenotabili del fornitore
        */
        $products = $supplier->products()->where('active', true)->get();
        $order->syncProducts($products, true);

        $this->attachModifiers($order);
        $this->resetOlderDates($order);
        $this->dispatchNotifications($order);
    }

    public function updated(Order $order)
    {
        if ($order->wasChanged('status')) {
            $this->dispatchNotifications($order);
        }

        if ($order->shipping) {
            Date::where('target_type', 'App\Supplier')->where('target_id', $order->supplier_id)->where('date', '<=', $order->shipping)->delete();
        }
    }

    public function deleting(Order $order)
    {
        foreach ($order->bookings as $booking) {
            $booking->deleteMovements();
        }

        $order->deleteMovements();
        $order->modifiers()->delete();

        return true;
    }

    public function deleted(Order $order)
    {
        $aggregate = Aggregate::find($order->aggregate_id);
        if ($aggregate->orders()->count() <= 0) {
            $aggregate->delete();
        }
    }
}
