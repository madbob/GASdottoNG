<?php

namespace App\Parameters\PaymentType;

class Sepa extends PaymentType
{
    public function identifier()
    {
        return 'sepa';
    }

    public function enabled()
    {
        return currentAbsoluteGas()->hasFeature('rid');
    }

    public function definition()
    {
        return (object) [
            'name' => __('movements.methods.sepa'),
            'identifier' => true,
            'icon' => 'cloud-plus',
            'active_for' => 'App\User',
            'valid_config' => function ($target) {
                return get_class($target) == 'App\User' && ! empty($target->rid['iban']);
            },
        ];
    }
}
