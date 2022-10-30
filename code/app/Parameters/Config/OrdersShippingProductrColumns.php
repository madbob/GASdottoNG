<?php

namespace App\Parameters\Config;

use App\Order;

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
		list($options, $values) = flaxComplexOptions(Order::formattableColumns('shipping'));
        return $values;
    }
}
