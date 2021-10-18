<?php

namespace App\Observers;

use App\Jobs\NotifyNewOrder;
use App\Jobs\NotifyClosedOrder;

use App\Aggregate;
use App\Order;
use App\Date;

class OrderObserver
{
    private function resetOlderDates($order)
    {
        $last_date = $order->shipping ? $order->shipping : $order->end;
        Date::where('target_type', 'App\Supplier')->where('target_id', $order->supplier_id)->where('date', '<=', $last_date)->delete();

        $recurrings = Date::where('target_type', 'App\Supplier')->where('target_id', $order->supplier_id)->where('recurring', '!=', '')->get();
        foreach($recurrings as $d) {
            $dates = $d->dates;
            $next_date = null;

            foreach($dates as $read_date) {
                if ($read_date > $last_date) {
                    $next_date = $read_date;
                    break;
                }
            }

            if (is_null($next_date)) {
                $d->delete();
            }
            else {
                $data = json_decode($d->recurring);
                $data->from = $next_date;
                $d->recurring = json_encode($data);
                $d->save();
            }
        }
    }

    public function created(Order $order)
    {
        $supplier = $order->supplier;

        /*
            Aggancio i prodotti attualmente prenotabili del fornitore
        */
        $order->products()->sync($supplier->products()->where('active', '=', true)->get());

        /*
            Aggancio all'ordine i modificatori attivi per il fornitore
        */
        foreach($supplier->modifiers as $mod) {
            if ($mod->active || $mod->always_on == true) {
                $new_mod = $mod->replicate();
                $new_mod->target_id = $order->id;
                $new_mod->target_type = get_class($order);
                $new_mod->save();
            }
        }

        $this->resetOlderDates($order);

        if ($order->status == 'open') {
            /*
                Nota bene: questo funziona solo in virtù del fatto che i job
                asincroni vengono eseguiti in differita. Infatti se l'ordine
                viene abilitato solo per alcuni luoghi di consegna questi
                vengono associati solo dopo la creazione dell'Order sul
                database, dunque solo dopo l'esecuzione di questa funzione si
                conosce l'elenco degli utenti che sono davvero da notificare
            */
            NotifyNewOrder::dispatch($order->id);
        }
    }

    public function updated(Order $order)
    {
        if ($order->wasChanged('status')) {
            if ($order->status == 'open') {
                NotifyNewOrder::dispatch($order->id);
            }
            else if ($order->status == 'closed') {
                NotifyClosedOrder::dispatch($order->id);
            }
        }

        if ($order->shipping) {
            Date::where('target_type', 'App\Supplier')->where('target_id', $order->supplier_id)->where('date', '<=', $order->shipping)->delete();
        }
    }

    public function deleting(Order $order)
    {
        foreach($order->bookings as $booking) {
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
