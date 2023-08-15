<?php

namespace App\Singletons;

use Illuminate\Support\Facades\DB;

use App\Order;

/*
    Questo serve a generare i numeri identificativi degli ordini.
    Essi non sono salvati permanentemente nel database onde evitare di dover
    ricalcolare tutto quando un ordine viene cancellato o le date sono
    modificate, ma sono dinamicamente generati in virtù di quanti ordini
    precedenti ci sono stati
*/
class OrderNumbersDispatcher
{
    private $cache = [];

    private function initCache($year)
    {
        if (array_key_exists($year, $this->cache) == false) {
            /*
                Questo di fatto serve solo ad eseguire gli unit test
            */
            $connection = config('database.default');
            $driver = config("database.connections.{$connection}.driver");
            $this->cache[$year] = Order::whereYear('start', $year)->orderBy('start', 'asc')->orderBy('id', 'asc')->pluck('start', 'id');
        }
    }

    public function getNumber($order)
    {
        $year = date('Y', strtotime($order->start));
        $this->initCache($year);

        $counter = 0;

        foreach($this->cache[$year] as $id => $start) {
            if ($start < $order->start || ($start == $order->start && $id < $order->id)) {
                $counter++;
                continue;
            }

            break;
        }

        return sprintf('%d / %d', $counter + 1, $year);
    }
}
