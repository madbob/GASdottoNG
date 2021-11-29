<?php

namespace App\Parameters\Config;

class OrdersDisplayColumns extends Config
{
    public function identifier()
    {
        return 'orders_display_columns';
    }

    public function type()
    {
        return 'array';
    }

    public function default()
    {
        return ['selection', 'name', 'price', 'quantity', 'total_price', 'quantity_delivered', 'price_delivered', 'notes'];
    }
}
