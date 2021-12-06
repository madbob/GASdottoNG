<?php

namespace App\Parameters\Config;

class Rid extends Config
{
    public function identifier()
    {
        return 'rid';
    }

    public function type()
    {
        return 'object';
    }

    public function default()
    {
        return (object) [
            'iban' => '',
            'id' => '',
            'org' => ''
        ];
    }
}
