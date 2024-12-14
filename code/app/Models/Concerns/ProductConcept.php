<?php

/*
    Questo trait accomuna Product e VariantCombo, che in molte circostanze
    vengono trattati alla stessa maniera (ovvero: l'unità atomica prenotabile
    nel contesto dell'ordine)
*/

namespace App\Models\Concerns;

trait ProductConcept
{
    abstract public function getConceptID();
}
