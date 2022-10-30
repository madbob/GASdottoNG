<?php

namespace App\Parameters\Config;

use App\Formatters\User;

class OrdersShippingUserColumns extends Config
{
    public function identifier()
    {
        return 'orders_shipping_user_columns';
    }

    public function type()
    {
        return 'array';
    }

    public function default()
    {
		list($options, $values) = flaxComplexOptions(User::formattableColumns());
        return $values;
    }
}
