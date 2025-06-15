<?php

namespace App\View\Icons;

use App\View\Icons\Concerns\Status;

class Supplier extends IconsMap
{
    use Status;

    private static function altIcons($ret, $user)
    {
        if ($user->can('supplier.add', $user->gas)) {
            $ret = self::statusIcons($ret);
        }

        return $ret;
    }

    public static function commons($user)
    {
        $ret = [
            'pencil' => (object) [
                'test' => function ($obj) use ($user) {
                    return $user->can('supplier.modify', $obj);
                },
                'text' => __('permissions.supplier.change'),
            ],
            'card-list' => (object) [
                'test' => function ($obj) use ($user) {
                    return $user->can('supplier.orders', $obj);
                },
                'text' => __('permissions.supplier.orders'),
            ],
            'arrow-down' => (object) [
                'test' => function ($obj) use ($user) {
                    return $user->can('supplier.shippings', $obj);
                },
                'text' => __('permissions.supplier.deliveries'),
            ],
        ];

        return self::altIcons($ret, $user);
    }
}
