<?php

/*
    Questa classe rappresenta un tipo di movimento contabile di default.
    Tutte le altre classi in App\Parameters\MovementType vengono usate dal
    seeder di base e - per i movimenti di sistema, che non sono eliminabili -
    forniscono le callback che permettono di costruire e rimuovere le referenze
    tra i movimenti contabili ed i soggetti coinvolti
*/

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
