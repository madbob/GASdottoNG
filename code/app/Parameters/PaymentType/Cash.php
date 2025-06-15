<?php

namespace App\Parameters\PaymentType;

class Cash extends PaymentType
{
    public function identifier()
    {
        return 'cash';
    }

    public function definition()
    {
        return (object) [
            'name' => __('movements.methods.cash'),
            'identifier' => false,
            'icon' => 'cash',
            'active_for' => null,
            'valid_config' => function ($target) {
                return true;
            },
        ];
    }
}
