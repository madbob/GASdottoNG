<?php

namespace App\Parameters\Config;

class UnmanagedShipping extends Config
{
    public function identifier()
    {
        return 'unmanaged_shipping';
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
