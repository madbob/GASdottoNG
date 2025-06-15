<?php

namespace App\Parameters\MovementType;

use App\Movement;

class DepositReturn extends MovementType
{
    public function identifier()
    {
        return 'deposit-return';
    }

    public function initNew($type)
    {
        $type->name = __('texts.movements.defaults.deposit_return');
        $type->sender_type = 'App\Gas';
        $type->target_type = 'App\User';
        $type->system = true;

        $type->function = json_encode($this->voidFunctions([
            (object) [
                'method' => 'cash',
                'sender' => $this->format(['cash' => 'decrement', 'deposits' => 'decrement']),
                'is_default' => true,
            ],
            (object) [
                'method' => 'bank',
                'sender' => $this->format(['bank' => 'decrement', 'deposits' => 'decrement']),
            ],
        ]));

        return $type;
    }

    public function systemInit($mov)
    {
        $mov->fixed_value = currentAbsoluteGas()->getConfig('deposit_amount');

        $mov->callbacks = [
            'post' => function (Movement $movement) {
                $movement->detachFromTarget('deposit_id');
            },
            'delete' => function (Movement $movement) {
                $sender = $movement->sender;

                if ($sender->deposit_id == 0) {
                    $payment = Movement::where('type', 'deposit-pay')->where('sender_id', $sender->id)->first();
                    if ($payment) {
                        $sender->deposit_id = $payment->id;
                        $sender->save();
                    }
                }
            },
        ];

        return $mov;
    }
}
