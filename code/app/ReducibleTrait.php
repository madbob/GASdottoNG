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

namespace App;

use Log;

trait ReducibleTrait
{
    /*
        Questi sono gli attributi essenziali che ci si aspetta di trovare nella
        riduzione di un oggetto
    */
    protected function describingAttributes()
    {
        return [
            'price',
            'weight',
            'quantity',
            'quantity_pieces',
            'price_delivered',
            'weight_delivered',
            'delivered',
            'delivered_pieces',
        ];
    }

    protected function describingAttributesMerge($first, $second, $sum = true)
    {
        if (is_null($first)) {
            return clone $second;
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
                    $ret->$merged[$to_merge->id] = $this->describingAttributesMerge($ret->$merged[$to_merge->id] ?? null, $to_merge);
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

    public static function mergeReduxData($first, $second)
    {
        if (is_null($first)) {
            return $second;
        }

        if (is_null($second)) {
            return $first;
        }

        $first = clone $first;
        $second = clone $second;

        $ref = new self();
        $behaviours = $ref->reduxBehaviour();

        $ret = $ref->describingAttributesMerge($first, $second);

        $merged = $behaviours->merged ?? '';
        if (!empty($merged)) {
            $merged_ids = array_unique(array_merge(array_keys($first->$merged), array_keys($second->$merged)));
            foreach($merged_ids as $id) {
                $ret->$merged[$id] = $ref->describingAttributesMerge($first->$merged[$id] ?? null, $second->$merged[$id] ?? null);
            }
        }

        unset($first);
        unset($second);

        return $ret;
    }
}
