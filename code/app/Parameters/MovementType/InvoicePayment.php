<?php

namespace App\Parameters\MovementType;

use App\Movement;

class InvoicePayment extends MovementType
{
    public function identifier()
    {
        return 'invoice-payment';
    }

    public function initNew($type)
    {
        $type->name = _i('Pagamento fattura a fornitore');
        $type->sender_type = 'App\Gas';
        $type->target_type = 'App\Invoice';
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
                $movement->attachToTarget('payment_id');
                $invoice = $movement->target;

                foreach($invoice->orders as $order) {
                    $order->payment_id = $movement->id;
                    $order->status = 'archived';
                    $order->save();
                }

                $invoice->status = 'payed';
                $invoice->save();
            },
            'delete' => function(Movement $movement) {
				$invoice = $movement->target;

                foreach($invoice->orders as $order) {
                    $order->payment_id = null;
                    $order->status = 'shipped';
                    $order->save();
                }

				$invoice->status = 'verified';
                $movement->detachFromTarget('payment_id');
            }
        ];

        return $mov;
    }
}
