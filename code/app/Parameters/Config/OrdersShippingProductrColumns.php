<?php

namespace App\Parameters\Config;

use App\Formatters\Order;

class OrdersShippingProductrColumns extends Config
{
    public function identifier()
    {
        return 'orders_shipping_product_columns';
    }

    public function type()
    {
        return 'array';
    }

    public function default()
    {
        [$options, $values] = flaxComplexOptions(Order::formattableColumns('shipping'));

        return $values;
    }
}
