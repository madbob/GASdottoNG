<?php

namespace App\View\Icons;

abstract class IconsMap
{
    /*
        Sovrascrivere questa funzione per introdurre icone selettive
    */
    public static function selective()
    {
        return [];
    }

    protected static function unrollStatuses($array, $statuses)
    {
        foreach($statuses as $identifier => $meta) {
            $array[$meta->icon] = (object) [
                'test' => function ($obj) use ($identifier) {
                    return $obj->status == $identifier;
                },
                'text' => $meta->label,
                'group' => 'status',
            ];
        }

        return $array;
    }

    /*
        Questa funzione deve ritornare un array associativo che contiene la
        definizione di ogni icona prevista per la classe in oggetto. Le chiavi
        dell'array sono i nomi delle icone Bootstrap da usare.
        Per avere il filtro ma non l'icona aggiungere il prefisso
        "hidden-" al nome.
    */
    public static abstract function commons($user);
}
