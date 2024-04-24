<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

use App\Date;
use App\Order;
use App\Aggregate;

class OpenOrders extends Command
{
    protected $signature = 'open:orders';
    protected $description = 'Controlla lo stato degli ordini da aprire automaticamente';

    private function getDates()
    {
        $dates = Date::where('type', 'order')->get();
        $today = date('Y-m-d');
        \Log::debug('Oggi ' . $today . ' ci sono ' . $dates->count() . ' date per apertura ordini');
        $aggregable = [];

        foreach($dates as $date) {
            try {
                $all_previous = true;

                foreach($date->order_dates as $d) {
                    \Log::debug($date->id . ' / ' . $d->start . ' / ' . $d->end . ' / ' . $d->shipping);

                    if ($d->start < $today) {
                        // @phpstan-ignore-next-line
                        $all_previous = $all_previous && true;
                    }
                    else if ($d->start > $today) {
                        $all_previous = false;
                    }
                    else if ($d->start == $today) {
                        \Log::debug('Data apertura ordine!');
                        $all_previous = false;

                        /*
                            Non cedere alla tentazione di spostare questo
                            controllo in cima al ciclo: arrivare fino a qui
                            serve a verificare se il set di ordini automatici è
                            scaduto o meno, e nel caso va eliminato
                        */
                        if ($date->suspend) {
                            \Log::debug('Data sospesa');
                            continue;
                        }

                        /*
                            Questo è per evitare di riaprire molteplici volte
                            l'ordine automatico: se già esiste un ordine aperto
                            oggi per il fornitore desiderato, passo oltre
                        */
                        $supplier = $d->target;
                        if (is_null($supplier) || $supplier->orders()->withoutGlobalScopes()->where('start', $today)->count() != 0) {
                            \Log::debug('Fornitore non trovato, o ordine già aperto');
                            continue;
                        }

                        $aggregable_key = sprintf('%s_%s', $d->end, $d->shipping);
                        if (!isset($aggregable[$aggregable_key])) {
                            $aggregable[$aggregable_key] = [];
                        }

                        $aggregable[$aggregable_key][] = $d;
                    }
                }

                if ($all_previous) {
                    Log::debug('Rimosso ordine ricorrente non più operativo: ' . $date->id);
                    $date->delete();
                }
            }
            catch(\Exception $e) {
                Log::error('Errore in apertura automatica ordine: ' . $e->getMessage());
            }
        }

        return $aggregable;
    }

    private function openByDates($aggregable)
    {
        foreach($aggregable as $aggr) {
            $aggregate = new Aggregate();
            $aggregate->save();

            foreach($aggr as $date) {
                $supplier = $date->target;

                $order = new Order();
                $order->aggregate_id = $aggregate->id;
                $order->supplier_id = $supplier->id;
                $order->comment = $date->comment;
                $order->status = 'suspended';
                $order->keep_open_packages = 'no';
                $order->start = $date->start;
                $order->end = $date->end;
                $order->shipping = $date->shipping;

                Log::debug('Apro ordine automatico per ' . $supplier->name);
                $order->save();

                $order->products()->sync($supplier->products()->where('active', '=', true)->get());

                $order->status = 'open';
                $order->save();
            }
        }
    }

    public function handle()
    {
        /*
            Qui vengono aperti gli ordini che erano stati impostati con una data
            futura
        */

        $pending = Order::withoutGlobalScopes()->where('status', 'suspended')->where('start', Carbon::today()->format('Y-m-d'))->get();
        foreach($pending as $p) {
            $p->status = 'open';
            $p->save();
        }

        /*
            Da qui vengono gestiti gli ordini schedulati con le date
        */

        $aggregable = $this->getDates();
        $this->openByDates($aggregable);
    }
}
