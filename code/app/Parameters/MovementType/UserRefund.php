<?php

namespace App\Parameters\MovementType;

use App\MovementType as MovementTypeModel;

class UserRefund extends MovementType
{
    public function identifier()
    {
        return 'user-refund';
    }

    public function create()
    {
        $type = new MovementTypeModel();

        $type->id = 'user-refund';
        $type->name = 'Rimborso spesa socio';
        $type->sender_type = 'App\Gas';
        $type->target_type = 'App\User';
        $type->fixed_value = null;
        $type->function = json_encode($this->voidFunctions([
            (object) [
                'method' => 'cash',
                'sender' => $this->format([
                    'cash' => 'decrement',
                    'gas' => 'decrement',
                ]),
            ],
            (object) [
                'method' => 'credit',
                'sender' => $this->format([
                    'gas' => 'decrement',
                ]),
                'target' => $this->format([
                    'bank' => 'increment',
                ]),
            ],
        ]));

        $type->save();
    }
}
