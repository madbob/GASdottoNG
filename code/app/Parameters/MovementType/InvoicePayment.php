<?php

namespace App\Parameters\MovementType;

use App\Movement;

class InvoicePayment extends OrderPayment
{
    public function identifier()
    {
        return 'invoice-payment';
    }

    public function initNew($type)
    {
        $type = parent::initNew($type);
        $type->name = __('texts.movements.defaults.invoice');
        $type->target_type = 'App\Invoice';

        return $type;
    }

    public function systemInit($mov)
    {
        $mov->callbacks = [
            'post' => function (Movement $movement) {
                $movement->attachToTarget('payment_id');
                $invoice = $movement->target;

                foreach ($invoice->orders as $order) {
                    $order->payment_id = $movement->id;
                    $order->status = 'archived';
                    $order->save();
                }

                $invoice->status = 'payed';
                $invoice->save();
            },
            'delete' => function (Movement $movement) {
                $invoice = $movement->target;

                foreach ($invoice->orders as $order) {
                    $order->payment_id = null;
                    $order->status = 'shipped';
                    $order->save();
                }

                $invoice->status = 'verified';
                $movement->detachFromTarget('payment_id');
            },
        ];

        return $mov;
    }
}
