<?php

namespace App\Parameters\MovementType;

class GenericPut extends MovementType
{
    public function identifier()
    {
        return 'generic-put';
    }

    public function initNew($type)
    {
        $type->name = 'Versamento sul conto';
        $type->sender_type = null;
        $type->target_type = 'App\Gas';

        $type->function = json_encode($this->voidFunctions([
            (object) [
                'method' => 'bank',
                'target' => $this->format(['bank' => 'increment', 'cash' => 'decrement']),
            ],
        ]));

        return $type;
    }
}
