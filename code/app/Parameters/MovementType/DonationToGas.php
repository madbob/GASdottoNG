<?php

namespace App\Parameters\MovementType;

class DonationToGas extends MovementType
{
    public function identifier()
    {
        return 'donation-to-gas';
    }

    public function initNew($type)
    {
        $type->name = __('movements.defaults.donation');
        $type->sender_type = 'App\User';
        $type->target_type = 'App\Gas';

        $type->function = json_encode($this->voidFunctions([
            (object) [
                'method' => 'cash',
                'target' => $this->format(['cash' => 'increment', 'gas' => 'increment']),
            ],
            (object) [
                'method' => 'bank',
                'target' => $this->format(['bank' => 'increment', 'gas' => 'increment']),
                'is_default' => true,
            ],
            (object) [
                'method' => 'credit',
                'sender' => $this->format(['bank' => 'decrement']),
                'target' => $this->format(['gas' => 'increment']),
            ],
        ]));

        return $type;
    }
}
