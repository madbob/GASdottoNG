<?php

namespace App\Parameters\MovementType;

use App\Movement;

class OrderPayment extends MovementType
{
    public function identifier()
    {
        return 'order-payment';
    }

    public function initNew($type)
    {
        $type->name = _i('Pagamento ordine a fornitore');
        $type->sender_type = 'App\Gas';
        $type->target_type = 'App\Order';
        $type->visibility = false;
        $type->system = true;

        $type->function = json_encode($this->voidFunctions([
            (object) [
                'method' => 'cash',
                'target' => $this->format(['bank' => 'decrement']),
                'sender' => $this->format(['cash' => 'decrement']),
            ],
            (object) [
                'method' => 'bank',
                'target' => $this->format(['bank' => 'decrement']),
                'sender' => $this->format(['bank' => 'decrement']),
                'is_default' => true,
            ]
        ]));

        return $type;
    }

    public function systemInit($mov)
    {
        $mov->callbacks = [
            'post' => function (Movement $movement) {
                $movement->attachToTarget();

                $order = $movement->target;
                $order->status = 'archived';
                $order->save();
            },
            'delete' => function(Movement $movement) {
                $movement->detachFromTarget();

                $order = $movement->target;
                $order->status = 'shipped';
                $order->save();
            }
        ];

        return $mov;
    }
}
