<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Log;

use App\Date;
use App\Order;
use App\Aggregate;

class OpenOrders extends Command
{
    protected $signature = 'open:orders';
    protected $description = 'Controlla lo stato degli ordini automatici';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $dates = Date::where('type', 'order')->get();
        $today = date('Y-m-d');
        $aggregable = [];

        foreach($dates as $date) {
            try {
                $all_previous = true;

                foreach($date->all_dates as $d) {
                    if ($d < $today) {
                        // @phpstan-ignore-next-line
                        $all_previous = $all_previous && true;
                    }
                    else if ($d > $today) {
                        $all_previous = false;
                    }
                    else if ($d == $today) {
                        $all_previous = false;

                        if ($date->suspend) {
                            continue;
                        }

                        /*
                            Questo è per evitare di riaprire molteplici volte
                            l'ordine automatico: se già esiste un ordine aperto
                            oggi per il fornitore desiderato, passo oltre
                        */
                        $supplier = $date->target;
                        if (is_null($supplier) || $supplier->orders()->where('start', $today)->count() != 0) {
                            continue;
                        }

                        $order = new Order();
                        $order->aggregate_id = 0;
                        $order->supplier_id = $supplier->id;
                        $order->comment = $date->comment;
                        $order->status = 'suspended';
                        $order->keep_open_packages = 'no';
                        $order->start = $today;
                        $order->end = date('Y-m-d', strtotime($today . ' +' . $date->end . ' days'));

                        if (!empty($date->shipping)) {
                            $order->shipping = date('Y-m-d', strtotime($today . ' +' . $date->shipping . ' days'));
                        }

                        Log::debug('Apro ordine automatico per ' . $supplier->name);
                        $order->save();

                        $order->products()->sync($supplier->products()->where('active', '=', true)->get());

                        $aggregable_key = sprintf('%s_%s', $date->end, $date->shipping);
                        if (!isset($aggregable[$aggregable_key])) {
                            $aggregable[$aggregable_key] = [];
                        }

                        $aggregable[$aggregable_key][] = $order;
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

        foreach($aggregable as $aggr) {
            $aggregate = new Aggregate();
            $aggregate->save();

            foreach($aggr as $order) {
                $order->aggregate_id = $aggregate->id;
                $order->status = 'open';
                $order->save();
            }
        }
    }
}
