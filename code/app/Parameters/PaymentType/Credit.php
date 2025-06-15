<?php

namespace App\Parameters\PaymentType;

class Credit extends PaymentType
{
    public function identifier()
    {
        return 'credit';
    }

    public function definition()
    {
        return (object) [
            'name' => __('texts.movements.methods.credit'),
            'identifier' => false,
            'icon' => 'person-badge',
            'active_for' => 'App\User',
            'valid_config' => function ($target) {
                return true;
            },
        ];
    }
}
