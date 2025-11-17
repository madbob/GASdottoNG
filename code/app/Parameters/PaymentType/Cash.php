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
            'name' => __('texts.movements.methods.cash'),
            'identifier' => false,
            'icon' => 'cash',
            'valid_config' => function ($target) {
                return true;
            },
        ];
    }
}
