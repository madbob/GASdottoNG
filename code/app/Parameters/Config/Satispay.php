<?php

namespace App\Parameters\Config;

class Satispay extends Config
{
    public function identifier()
    {
        return 'satispay';
    }

    public function type()
    {
        return 'object';
    }

    public function default()
    {
        return (object) [
            'public' => '',
            'secret' => '',
            'key' => '',
        ];
    }
}
