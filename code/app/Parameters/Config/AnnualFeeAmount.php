<?php

namespace App\Parameters\Config;

class AnnualFeeAmount extends Config
{
    public function identifier()
    {
        return 'annual_fee_amount';
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
