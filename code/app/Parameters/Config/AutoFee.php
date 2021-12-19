<?php

namespace App\Parameters\Config;

class AutoFee extends Config
{
    public function identifier()
    {
        return 'auto_fee';
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
