<?php

namespace App\Parameters\MovementType;

class UserCredit extends MovementType
{
    public function identifier()
    {
        return 'user-credit';
    }

    public function initNew($type)
    {
        $type->name = 'Deposito di credito da parte di un socio';
        $type->sender_type = null;
        $type->target_type = 'App\User';
        $type->fixed_value = null;
        $type->system = true;

        $type->function = json_encode($this->voidFunctions([
            (object) [
                'method' => 'cash',
                'target' => $this->format(['bank' => 'increment']),
                'master' => $this->format(['cash' => 'increment']),
            ],
            (object) [
                'method' => 'bank',
                'target' => $this->format(['bank' => 'increment']),
                'master' => $this->format(['bank' => 'increment']),
            ],
            (object) [
                'method' => 'paypal',
                'target' => $this->format(['bank' => 'increment']),
                'master' => $this->format(['paypal' => 'increment']),
            ]
        ]));

        return $type;
    }
}
