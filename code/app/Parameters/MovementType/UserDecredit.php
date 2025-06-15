<?php

namespace App\Parameters\MovementType;

class UserDecredit extends MovementType
{
    public function identifier()
    {
        return 'user-decredit';
    }

    public function initNew($type)
    {
        $type->name = __('movements.defaults.decredit');
        $type->sender_type = 'App\User';
        $type->target_type = null;

        $type->function = json_encode($this->voidFunctions([
            (object) [
                'method' => 'cash',
                'sender' => $this->format(['bank' => 'decrement']),
                'master' => $this->format(['cash' => 'decrement']),
            ],
            (object) [
                'method' => 'bank',
                'sender' => $this->format(['bank' => 'decrement']),
                'master' => $this->format(['bank' => 'decrement']),
                'is_default' => true,
            ],
        ]));

        return $type;
    }
}
