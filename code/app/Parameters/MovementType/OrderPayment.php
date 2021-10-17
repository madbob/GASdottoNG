<?php

namespace App\Parameters\MovementType;

use App\MovementType as MovementTypeModel;
use App\Movement;

class OrderPayment extends MovementType
{
    public function identifier()
    {
        return 'order-payment';
    }

    public function create()
    {
        $type = new MovementTypeModel();

        $type->id = 'order-payment';
        $type->name = 'Pagamento ordine a fornitore';
        $type->sender_type = 'App\Gas';
        $type->target_type = 'App\Order';
        $type->fixed_value = null;
        $type->visibility = false;
        $type->system = true;
        $type->function = json_encode($this->voidFunctions([
            (object) [
                'method' => 'cash',
                'target' => $this->format([
                    'bank' => 'decrement',
                ]),
                'sender' => $this->format([
                    'cash' => 'decrement',
                    'suppliers' => 'decrement',
                ]),
            ],
            (object) [
                'method' => 'bank',
                'target' => $this->format([
                    'bank' => 'decrement',
                ]),
                'sender' => $this->format([
                    'bank' => 'decrement',
                    'suppliers' => 'decrement',
                ]),
            ]
        ]));

        $type->save();
    }

    public function systemInit($mov)
    {
        $mov->callbacks = [
            'post' => function (Movement $movement) {
                $movement->attachToTarget();
            },
            'delete' => function(Movement $movement) {
                $movement->detachFromTarget();
            }
        ];

        return $mov;
    }
}
