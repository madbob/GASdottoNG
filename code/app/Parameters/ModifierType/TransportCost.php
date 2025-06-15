<?php

namespace App\Parameters\ModifierType;

use App\Product;
use App\Supplier;
use App\Circle;

class TransportCost extends ModifierType
{
    public function identifier()
    {
        return 'spese-trasporto';
    }

    public function initNew($type)
    {
        $type->name = __('modifiers.defaults.delivery');
        $type->system = true;
        $type->identifier = 'shipping';
        $type->classes = [Product::class, Supplier::class, Circle::class];

        return $type;
    }
}
