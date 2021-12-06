<?php

namespace App\Parameters\Config;

class AutoUserOrderSummary extends Config
{
    public function identifier()
    {
        return 'auto_user_order_summary';
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
