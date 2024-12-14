<?php

namespace App\Parameters\ModifierType;

use App\Product;
use App\Supplier;
use App\Delivery;

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
        $type->identifier = 'shipping';
        $type->classes = [Product::class, Supplier::class, Delivery::class];

        return $type;
    }
}
