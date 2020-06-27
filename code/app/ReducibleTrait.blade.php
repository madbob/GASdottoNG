<?php

namespace App;

use Log;

trait ReducibleTrait
{
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
            return $second;
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
        $behaviours = $this->reduxBehaviour();
        $collected = $behaviours->collected;
        $children = ($behaviours->children)($this, $filters);
        $children_key = $behaviours->master_key;

        foreach($children as $child) {
            $child = ($behaviours->optimize)($this, $child);
            $reduxed_child = $child->reduxData(null, $filters);

            if (!isset($ret->$collected[$reduxed_child->id])) {
                $ret->$collected[$reduxed_child->id] = $reduxed_child;
            }
            else {
                $ret->$collected[$reduxed_child->id] = $this->describingAttributesMerge($ret->$collected[$reduxed_child->id], $reduxed_child);
            }

            $ret = $this->describingAttributesMerge($ret, $reduxed_child);

            if (isset($behaviours->merged)) {
                foreach($behaviours->merged as $merged) {
                    foreach($reduxed_child->$merged as $to_merge) {
                        $ret->$merged[$to_merge->id] = $this->describingAttributesMerge($ret->$merged[$to_merge->id] ?? null, $to_merge);
                    }
                }
            }
        }

        return $ret;
    }

    protected function emptyReduxBehaviour()
    {
        return (object) [
            'master_key' => 'id',
            'merged' => [],

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

            if (isset($behaviours->merged)) {
                foreach($behaviours->merged as $merged) {
                    $ret->$merged = [];
                }
            }
        }

        return $this->descendReduction($ret, $filters);
    }
}
