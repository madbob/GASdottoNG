<?php

namespace App\Parameters\Config;

class Paypal extends Config
{
    public function identifier()
    {
        return 'paypal';
    }

    public function type()
    {
        return 'object';
    }

    public function default()
    {
        return (object) [
            'client_id' => '',
            'secret' => '',
            'mode' => 'sandbox'
        ];
    }
}
