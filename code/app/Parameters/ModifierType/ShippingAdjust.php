<?php

/*
    Questo tipo di modificatore non deve essere mai esposto né configurato
    dall'utente.
    Se vengono abilitate le consegne senza quantità, viene usato per tenere
    traccia della differenza tra prenotato e consegnato in valore assoluto.
*/

namespace App\Parameters\ModifierType;

class ShippingAdjust extends ModifierType
{
    public function identifier()
    {
        return 'arrotondamento-consegna';
    }

    public function initNew($type)
    {
        $type->name = __('modifiers.defaults.rounding');
        $type->system = true;
        $type->hidden = true;
        $type->classes = [];

        return $type;
    }
}
