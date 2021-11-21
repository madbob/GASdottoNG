<?php

/*
    Questa classe rappresenta un tipo di movimento contabile di default.
    Tutte le altre classi in App\Parameters\MovementType vengono usate dal
    seeder di base e - per i movimenti di sistema, che non sono eliminabili -
    forniscono le callback che permettono di costruire e rimuovere le referenze
    tra i movimenti contabili ed i soggetti coinvolti
*/

namespace App\Parameters\MovementType;

use App\MovementType as MovementTypeModel;

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

    /*
        Questa deve essere sovrascritta per forzare le callback da attivare per
        la manipolazione dei movimenti
    */
    public function systemInit($mov)
    {
        return $mov;
    }

    public function create()
    {
        $type = new MovementTypeModel();
        $type->id = $this->identifier();

        $type->fixed_value = null;

        $type = $this->initNew($type);
        $type->save();
    }

    public abstract function identifier();
    public abstract function initNew($type);
}
