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

    /*
        Questa funzione deve ritornare un array associativo che contiene la
        definizione di ogni icona prevista per la classe in oggetto. Le chiavi
        dell'array sono i nomi delle icone Bootstrap da usare.
        Per avere il filtro ma non l'icona aggiungere il prefisso
        "hidden-" al nome.
    */
    public static abstract function commons($user);
}
