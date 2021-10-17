<?php

namespace App\Parameters\MovementType;

use App\MovementType as MovementTypeModel;

class GenericPut extends MovementType
{
    public function identifier()
    {
        return 'generic-put';
    }

    public function create()
    {
        $type = new MovementTypeModel();

        $type->id = 'generic-put';
        $type->name = 'Versamento sul conto';
        $type->sender_type = null;
        $type->target_type = 'App\Gas';
        $type->fixed_value = null;
        $type->function = json_encode($this->voidFunctions([
            (object) [
                'method' => 'bank',
                'target' => $this->format([
                    'bank' => 'increment',
                    'cash' => 'decrement',
                ]),
            ],
        ]));

        $type->save();
    }
}
