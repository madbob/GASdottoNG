<?php

namespace App\Parameters\Config;

class HasMultiGAS extends Config
{
    public function identifier()
    {
        return 'multigas';
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
