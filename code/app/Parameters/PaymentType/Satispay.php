<?php

namespace App\Parameters\PaymentType;

class Satispay extends PaymentType
{
    public function identifier()
    {
        return 'satispay';
    }

    public function enabled()
    {
        return (currentAbsoluteGas()->hasFeature('satispay'));
    }

    public function definition()
    {
        return (object) [
            'name' => _i('Satispay'),
            'identifier' => true,
            'icon' => 'cloud-plus',
            'active_for' => 'App\User',
            'valid_config' => function($target) {
                return true;
            }
        ];
    }
}
