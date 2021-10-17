<?php

namespace App\Parameters\MovementType;

use App\MovementType as MovementTypeModel;

class UserDecredit extends MovementType
{
    public function identifier()
    {
        return 'user-decredit';
    }

    public function create()
    {
        $type = new MovementTypeModel();

        $type->id = 'user-decredit';
        $type->name = 'Reso credito per un socio';
        $type->sender_type = 'App\User';
        $type->target_type = null;
        $type->fixed_value = null;
        $type->function = json_encode($this->voidFunctions([
            (object) [
                'method' => 'cash',
                'sender' => $this->format([
                    'bank' => 'decrement',
                ]),
                'master' => $this->format([
                    'cash' => 'decrement',
                ]),
            ],
            (object) [
                'method' => 'bank',
                'sender' => $this->format([
                    'bank' => 'decrement',
                ]),
                'master' => $this->format([
                    'bank' => 'decrement',
                ]),
            ]
        ]));

        $type->save();
    }
}
