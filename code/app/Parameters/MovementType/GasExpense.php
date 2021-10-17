<?php

namespace App\Parameters\MovementType;

class GasExpense extends MovementType
{
    public function identifier()
    {
        return 'gas-expense';
    }

    public function initNew($type)
    {
        $type->name = 'Acquisto/spesa GAS';
        $type->sender_type = 'App\Gas';
        $type->target_type = null;
        $type->fixed_value = null;

        $type->function = json_encode($this->voidFunctions([
            (object) [
                'method' => 'cash',
                'sender' => $this->format(['cash' => 'decrement', 'gas' => 'decrement']),
            ],
            (object) [
                'method' => 'bank',
                'sender' => $this->format(['bank' => 'decrement', 'gas' => 'decrement']),
            ],
        ]));

        return $type;
    }
}
