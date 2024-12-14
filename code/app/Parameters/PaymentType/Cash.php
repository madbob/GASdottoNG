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
            'name' => _i('Contanti'),
            'identifier' => false,
            'icon' => 'cash',
            'active_for' => null,
            'valid_config' => function ($target) {
                return true;
            },
        ];
    }
}
