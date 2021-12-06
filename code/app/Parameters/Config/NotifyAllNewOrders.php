<?php

namespace App\Parameters\Config;

class NotifyAllNewOrders extends Config
{
    public function identifier()
    {
        return 'notify_all_new_orders';
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
