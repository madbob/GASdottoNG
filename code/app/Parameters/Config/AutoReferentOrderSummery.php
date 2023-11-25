<?php

namespace App\Parameters\Config;

class AutoReferentOrderSummery extends Config
{
    public function identifier()
    {
        return 'auto_referent_order_summary';
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
