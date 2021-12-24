<?php

namespace App;

trait LeafReducibleTrait
{
    use ReducibleTrait;

    public function relativeRedux($ret)
    {
        $status = $this->status;

        if ($status == 'shipped' || $status == 'saved') {
            $ret->relative_price = $ret->price_delivered;
            $ret->relative_weight = $ret->weight_delivered;
            $ret->relative_quantity = $ret->delivered;
            $ret->relative_pieces = $ret->delivered_pieces;
        }
        else {
            $ret->relative_price = $ret->price;
            $ret->relative_weight = $ret->weight;
            $ret->relative_quantity = $ret->quantity;
            $ret->relative_pieces = $ret->quantity_pieces;
        }

        return $ret;
    }
}
