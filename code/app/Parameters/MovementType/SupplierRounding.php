<?php

namespace App\Parameters\MovementType;

class SupplierRounding extends MovementType
{
    public function identifier()
    {
        return 'supplier-rounding';
    }

    public function initNew($type)
    {
        $type->name = 'Arrotondamento/sconto fornitore';
        $type->sender_type = 'App\Supplier';
        $type->target_type = 'App\Gas';
        $type->allow_negative = true;
        $type->fixed_value = null;

        $type->function = json_encode($this->voidFunctions([
            (object) [
                'method' => 'cash',
                'sender' => $this->format(['bank' => 'decrement']),
                'target' => $this->format(['gas' => 'increment']),
            ]
        ]));

        return $type;
    }
}
