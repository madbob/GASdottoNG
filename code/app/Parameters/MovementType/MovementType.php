<?php

/*
    Questa classe rappresenta un tipo di movimento contabile di default.
    Tutte le altre classi in App\Parameters\MovementType vengono usate dal
    seeder di base e - per i movimenti di sistema, che non sono eliminabili -
    forniscono le callback che permettono di costruire e rimuovere le referenze
    tra i movimenti contabili ed i soggetti coinvolti
*/

namespace App\Parameters\MovementType;

use App\Parameters\Parameter;
use App\MovementType as MovementTypeModel;

abstract class MovementType extends Parameter
{
    /*
        L'unico scopo di questa funzione è semplificare la definizione dei tipi
        di movimento contabile definiti di default, inizializzando sempre tutti
        gli array che descrivono le operazioni da compiere sui bilanci per
        ciascun soggetto toccato. In questo modo si può assumere che siano
        sempre presenti, benché vuoti
    */
    protected function voidFunctions($array)
    {
        foreach (array_keys($array) as $i) {
            foreach (['sender', 'target', 'master'] as $t) {
                $array[$i]->$t ??= (object) [
                    'operations' => [],
                ];
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

    abstract public function initNew($type);
}
