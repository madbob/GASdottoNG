<?php

/*
    Questo singleton impatta sul comportamento di
    Product::canAggregateQuantities(), forzando l'aggregazione delle quantità
    delle prenotazioni (in particolare tra utenti e amici).
    Da usare in casi molto specifici, solitamente per rappresentare le quantità
    in modo informale
*/

namespace App\Singletons;

class AggregationSwitch
{
    private $enforce_aggregation = false;

    public function setEnforced($active)
    {
        $this->enforce_aggregation = $active;
    }

    public function isEnforced()
    {
        return $this->enforce_aggregation;
    }
}
