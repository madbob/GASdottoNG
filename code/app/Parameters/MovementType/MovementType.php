<?php

namespace App\Parameters\MovementType;

abstract class MovementType
{
    protected function voidFunctions($array)
    {
        foreach($array as $i => $a) {
            foreach(['sender', 'target', 'master'] as $t) {
                if (!isset($a->$t)) {
                    $array[$i]->$t = (object) [
                        'operations' => []
                    ];
                }
            }
        }

        return $array;
    }

    protected function format($ops)
    {
        $ret = (object) [
            'operations' => [],
        ];

        foreach ($ops as $field => $op) {
            $ret->operations[] = (object) [
                'operation' => $op,
                'field' => $field,
            ];
        }

        return $ret;
    }

    public function systemInit($mov)
    {
        return $mov;
    }

    public abstract function identifier();
    public abstract function create();
}
