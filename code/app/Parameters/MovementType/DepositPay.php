<?php

namespace App\Parameters\MovementType;

use App\Movement;

class DepositPay extends MovementType
{
    public function identifier()
    {
        return 'deposit-pay';
    }

    public function initNew($type)
    {
        $type->name = _i('Deposito cauzione socio del GAS');
        $type->sender_type = 'App\User';
        $type->target_type = 'App\Gas';
        $type->visibility = false;
        $type->system = true;

        $type->function = json_encode($this->voidFunctions([
            (object) [
                'method' => 'cash',
                'target' => $this->format(['cash' => 'increment', 'deposits' => 'increment']),
                'is_default' => true,
            ],
            (object) [
                'method' => 'bank',
                'target' => $this->format(['bank' => 'increment', 'deposits' => 'increment']),
            ],
            (object) [
                'method' => 'credit',
                'sender' => $this->format(['bank' => 'decrement']),
                'target' => $this->format(['deposits' => 'increment']),
            ],
        ]));

        return $type;
    }

    public function systemInit($mov)
    {
        $mov->fixed_value = currentAbsoluteGas()->getConfig('deposit_amount');

        $mov->callbacks = [
            'post' => function (Movement $movement) {
                $movement->attachToSender('deposit_id');
            },
            'delete' => function (Movement $movement) {
                $movement->detachFromSender('deposit_id');
            },
        ];

        return $mov;
    }
}
