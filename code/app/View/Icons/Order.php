<?php

namespace App\View\Icons;

use App\Helpers\Status;

class Order extends IconsMap
{
    public static function commons($user)
    {
        $ret = [
            'plus-circle' => (object) [
                'test' => function ($obj) {
                    return $obj->inPendingPackageState();
                },
                'text' => __('texts.orders.pending_packages'),
                'group' => 'status',
            ],
        ];

        $ret = self::unrollStatuses($ret, Status::orders());

        return $ret;
    }
}
