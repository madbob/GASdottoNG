<?php

namespace App\Parameters\PaymentType;

class PayPal extends PaymentType
{
    public function identifier()
    {
        return 'paypal';
    }

    public function enabled()
    {
        return (currentAbsoluteGas()->hasFeature('paypal'));
    }

    public function definition()
    {
        return (object) [
            'name' => _i('PayPal'),
            'identifier' => true,
            'icon' => 'cloud-plus',
            'active_for' => 'App\User',
            'valid_config' => function($target) {
                return true;
            }
        ];
    }
}
