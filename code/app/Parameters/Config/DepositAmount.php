<?php

namespace App\Parameters\Config;

class DepositAmount extends Config
{
    public function identifier()
    {
        return 'deposit_amount';
    }

    public function type()
    {
        return 'float';
    }

    public function default()
    {
        return 10.00;
    }
}
