<?php

namespace App\Parameters\Config;

class OrdersShippingSeparateFriends extends Config
{
    public function identifier()
    {
        return 'orders_shipping_separate_friends';
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
