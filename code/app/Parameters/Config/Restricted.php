<?php

namespace App\Parameters\Config;

class Restricted extends Config
{
    public function identifier()
    {
        return 'restricted';
    }

    public function type()
    {
        return 'boolean';
    }

    public function default()
    {
        return 0;
    }
}
