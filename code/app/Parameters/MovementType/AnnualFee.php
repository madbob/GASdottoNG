<?php

namespace App\Parameters\MovementType;

use App\Movement;

class AnnualFee extends MovementType
{
    public function identifier()
    {
        return 'annual-fee';
    }

    public function initNew($type)
    {
        $type->name = __('movements.defaults.fee');
        $type->sender_type = 'App\User';
        $type->target_type = 'App\Gas';
        $type->visibility = false;
        $type->system = true;

        $type->function = json_encode($this->voidFunctions([
            (object) [
                'method' => 'cash',
                'target' => $this->format(['cash' => 'increment', 'gas' => 'increment']),
                'is_default' => true,
            ],
            (object) [
                'method' => 'bank',
                'target' => $this->format(['bank' => 'increment', 'gas' => 'increment']),
            ],
            (object) [
                'method' => 'credit',
                'sender' => $this->format(['bank' => 'decrement']),
                'target' => $this->format(['gas' => 'increment']),
            ],
        ]));

        return $type;
    }

    public function systemInit($mov)
    {
        $mov->fixed_value = currentAbsoluteGas()->getConfig('annual_fee_amount');

        $mov->callbacks = [
            'post' => function (Movement $movement) {
                $movement->attachToSender('fee_id');
            },
            'delete' => function (Movement $movement) {
                $movement->detachFromSender('fee_id');
            },
        ];

        return $mov;
    }
}
