<?php

namespace App\Parameters\PaymentType;

class Bank extends PaymentType
{
    public function identifier()
    {
        return 'bank';
    }

    public function definition()
    {
        return (object) [
            'name' => __('texts.movements.methods.bank'),
            'identifier' => true,
            'icon' => 'bank',
            'active_for' => null,
            'valid_config' => function ($target) {
                return true;
            },
        ];
    }
}
