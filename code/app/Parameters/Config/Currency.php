<?php

namespace App\Parameters\Config;

class Currency extends Config
{
    public function identifier()
    {
        return 'currency';
    }

    public function type()
    {
        return 'string';
    }

    public function default()
    {
        return '€';
    }
}
