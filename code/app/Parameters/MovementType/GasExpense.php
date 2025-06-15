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
        $type->name = __('texts.movements.defaults.expense');
        $type->sender_type = 'App\Gas';
        $type->target_type = null;

        $type->function = json_encode($this->voidFunctions([
            (object) [
                'method' => 'cash',
                'sender' => $this->format(['cash' => 'decrement', 'gas' => 'decrement']),
            ],
            (object) [
                'method' => 'bank',
                'sender' => $this->format(['bank' => 'decrement', 'gas' => 'decrement']),
                'is_default' => true,
            ],
        ]));

        return $type;
    }
}
