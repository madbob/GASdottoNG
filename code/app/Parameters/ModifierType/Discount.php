<?php

namespace App\Parameters\ModifierType;

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
        $type->classes = ['App\Product', 'App\Supplier'];
        return $type;
    }
}
