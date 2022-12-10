<?php

/*
    Questa è la classe essenziale che permette la "riduzione" delle informazioni
    all'interno di un ordine e delle sue prenotazioni.
    Aggregati, ordini, prenotazioni, prodotti prenotati e loro varianti fanno
    tutti capo a questa struttura dati, che riassume in modo omogeneo quantità e
    prezzi. Più precisamente: un aggregato è la somma delle riduzioni dei suoi
    ordini, i quali sono la somma delle riduzioni delle sue prenotazioni, le
    quali sono la somma delle riduzioni dei loro prodotti, i quali possono
    eventualmente essere la somma delle riduzione delle loro varianti.
    Questo per semplificare il calcolo dei valori complessivi, e dunque
    l'applicazione dei modificatori o la generazione delle esportazioni.
    Le classi la cui riduzione non dipende da altri elementi (tipicamente: le
    foglie dell'albero di riduzione, ovvero i prodotti senza varianti o le
    varianti) devono sovrascrivere la funzione reduxData() per restituire
    direttamente la loro propria rappresentazione, che andrà a essere sommata a
    tutte le altre.
*/

namespace App\Models\Concerns;

use Log;

use App\Aggregate;
use App\Order;
use App\Booking;
use App\BookedProduct;

trait ReducibleTrait
{
    /*
        Questi sono gli attributi essenziali che ci si aspetta di trovare nella
        riduzione di un oggetto
    */
    protected function describingAttributes()
    {
        return [
            /*
                Dati relativi al prenotato
            */
            'price',
            'weight',
            'quantity',
            'quantity_pieces',

            /*
                Dati relativi al consegnato
            */
            'price_delivered',
            'weight_delivered',
            'delivered',
            'delivered_pieces',

            /*
                Se la prenotazione/prodotto è consegnata qui vengono accumulati
                i valori del consegnato, altrimenti quelli del prenotato.
                Queste informazioni relative, che variano durante la fase di
                consegna dell'ordine, servono a calcolare nel modo più accurato
                possibile il valore dinamico dei modificatori trasversali
            */
            'relative_price',
            'relative_weight',
            'relative_quantity',
            'relative_pieces',
        ];
    }

    /*
        In fase di popolamento dell'array "merged" della riduzione, questi sono
        i sotto-array che devono (se presenti) essere a loro volta essere
        mergiati
    */
    protected function subArrayMerge()
    {
        return [
            'variants',
        ];
    }

    /*
        Date due riduzioni (ad esempio, di due prenotazioni) questa funzione
        provvede a sommare tra di loro i valori enumerati in
        describingAttributes() per ottenere la riduzione complessiva
    */
    protected function describingAttributesMerge($first, $second, $sum = true)
    {
        if (is_null($first)) {
            return clone $second;
        }

        if (is_null($second)) {
            return $first;
        }

        foreach ($this->describingAttributes() as $attr) {
            if (!isset($first->$attr)) {
                $first->$attr = 0;
            }

            if (!isset($second->$attr)) {
                continue;
            }

            if ($sum) {
                $first->$attr += $second->$attr;
            }
            else {
                $first->$attr -= $second->$attr;
            }
        }

        return $first;
    }

    /*
        Come describingAttributesMerge(), ma in più condensa anche i sotto-array
        enumerati in subArrayMerge() delle due riduzioni.
        Funzione introdotta per condensare le varianti dei prodotti presenti in
        diverse prenotazioni (che vengono ridotte indipendentemente tra loro)
    */
    protected function deepMergingAttributes($child, $first, $second, $sum = true)
    {
        $ret = $this->describingAttributesMerge($first, $second, $sum);

        foreach ($this->subArrayMerge() as $subarray) {
            if (!isset($first->$subarray) && !isset($second->$subarray)) {
                continue;
            }

            $first_subarray = $first->$subarray ?? [];
            $second_subarray = $second->$subarray ?? [];
            $final = [];
            $ids = array_unique(array_merge(array_keys($first_subarray), array_keys($second_subarray)));

            foreach($ids as $id) {
                $final[$id] = $this->describingAttributesMerge($first_subarray[$id] ?? null, $second_subarray[$id] ?? null);
            }

            $ret->$subarray = $final;
        }

        return $ret;
    }

    protected function descendReduction($ret, $filters)
    {
        foreach ($this->describingAttributes() as $attr) {
            if (!isset($ret->$attr)) {
                $ret->$attr = 0;
            }
        }

        $behaviours = $this->reduxBehaviour();
        $collected = $behaviours->collected;
        $children = ($behaviours->children)($this, $filters);
        $children_key = $behaviours->master_key;

        foreach($children as $child) {
            $child = ($behaviours->optimize)($this, $child);
            $reduxed_child = $child->reduxData(null, $filters);
            $ret->$collected[$reduxed_child->id] = $this->describingAttributesMerge($ret->$collected[$reduxed_child->id] ?? null, $reduxed_child);
            $ret = $this->describingAttributesMerge($ret, $reduxed_child);

            $merged = $behaviours->merged ?? '';
            if (!empty($merged)) {
                foreach($reduxed_child->$merged as $to_merge) {
                    $ret->$merged[$to_merge->id] = $this->deepMergingAttributes($child, $ret->$merged[$to_merge->id] ?? null, $to_merge);
                }
            }
        }

        return $ret;
    }

    protected function emptyReduxBehaviour()
    {
        return (object) [
            'master_key' => 'id',
            'merged' => '',

            'optimize' => function($master, $child) {
                return $child;
            }
        ];
    }

    /*
        Questa funzione permette di ricostruire la riduzione in funzione di una
        collezione di modificatori, che abbisognano solo di determinati dati (e
        non dell'intero albero dell'aggregato)
    */
    public function minimumRedux($modifiers)
    {
        $aggregate = null;
        $order = null;
        $booking = null;

        switch(get_class($this)) {
            case Aggregate::class:
                $aggregate = $this;
                $order = null;
                $booking = null;
                break;

            case Order::class:
                $aggregate = $this->aggregate;
                $order = $this;
                $booking = null;
                break;

            case Booking::class:
                $aggregate = $this->order->aggregate;
                $order = $this->order;
                $booking = $this;
                break;

            case BookedProduct::class:
                $aggregate = $this->booking->order->aggregate;
                $order = $this->booking->order;
                $booking = $this->booking;
                break;

            default:
                \Log::error('Unrecognized class calling minimum reduction: ' . get_class($this));
                break;
        }

        $priority = ['product', 'booking', 'order', 'aggregate'];
        $target_priority = -1;

        foreach($modifiers as $mod) {
            $p = array_search($mod->applies_target, $priority);
            if ($p > $target_priority) {
                $target_priority = $p;
            }
        }

        $aggregate_data = null;

        if ($target_priority <= 1 && ($booking && $order)) {
            $aggregate_data = $aggregate->reduxData(null, [
                'orders' => [$order],
                'bookings' => [$booking]
            ]);
        }
        else {
            $target_priority = 2;
        }

        if (is_null($aggregate_data)) {
            if ($target_priority == 2 && $order) {
                $aggregate_data = $aggregate->reduxData(null, [
                'orders' => [$order]
                ]);
            }
            else {
                $target_priority = 3;
            }

            if ($target_priority == 3) {
                $aggregate_data = $aggregate->reduxData();
            }
        }

        return $aggregate_data;
    }

    /*
        Reminder: è sconsigliato cachare i risultati dell'operazione di
        riduzione, esistendo diverse variabili (incluso il GAS attualmente
        attivo nel GlobalScopeHub)
    */
    public function reduxData($ret = null, $filters = null)
    {
        if (is_null($ret)) {
            $behaviours = $this->reduxBehaviour();
            $master_key = $behaviours->master_key;

            $ret = (object) [
                'id' => $this->$master_key,
            ];

            if (isset($behaviours->collected)) {
                $collected = $behaviours->collected;
                $ret->$collected = [];
            }

            $merged = $behaviours->merged ?? '';
            if (!empty($merged)) {
                $ret->$merged = [];
            }
        }

        return $this->descendReduction($ret, $filters);
    }

    abstract protected function reduxBehaviour();
}
