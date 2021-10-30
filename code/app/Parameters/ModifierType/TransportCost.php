<?php

namespace App\Parameters\ModifierType;

class TransportCost extends ModifierType
{
    public function identifier()
    {
        return 'spese-trasporto';
    }

    public function initNew($type)
    {
        $type->name = _i('Spese Trasporto');
        $type->system = true;
        $type->classes = ['App\Product', 'App\Supplier', 'App\Delivery'];
        return $type;
    }
}
