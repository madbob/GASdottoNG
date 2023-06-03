<?php

namespace App\Parameters\MovementType;

class UserRefund extends MovementType
{
    public function identifier()
    {
        return 'user-refund';
    }

    public function initNew($type)
    {
        $type->name = _i('Rimborso spesa socio');
        $type->sender_type = 'App\Gas';
        $type->target_type = 'App\User';

        $type->function = json_encode($this->voidFunctions([
            (object) [
                'method' => 'cash',
                'sender' => $this->format(['cash' => 'decrement', 'gas' => 'decrement']),
            ],
            (object) [
                'method' => 'credit',
                'sender' => $this->format(['gas' => 'decrement']),
                'target' => $this->format(['bank' => 'increment']),
                'is_default' => true,
            ],
        ]));

        return $type;
    }
}
