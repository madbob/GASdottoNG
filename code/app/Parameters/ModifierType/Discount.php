<?php

namespace App\Parameters\ModifierType;

use App\Product;
use App\Supplier;

class Discount extends ModifierType
{
    public function identifier()
    {
        return 'sconto';
    }

    public function initNew($type)
    {
        $type->name = _i('Sconto');
        $type->system = true;
        $type->identifier = 'discount';
        $type->classes = [Product::class, Supplier::class];

        return $type;
    }
}
