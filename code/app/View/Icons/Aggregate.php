<?php

namespace App\View\Icons;

use App\Helpers\Status;
use App\Gas;

class Aggregate extends IconsMap
{
    public static function commons($user)
    {
        $ret = [
            'plus-circle' => (object) [
                'test' => function ($obj) {
                    return $obj->status == 'closed' && $obj->hasPendingPackages();
                },
                'text' => __('texts.orders.pending_packages'),
            ],
        ];

        $ret = self::unrollStatuses($ret, Status::orders());

        $gas_count = Gas::count();
        if ($gas_count > 1) {
            $ret['share'] = (object) [
                'test' => function ($obj) {
                    return $obj->gas()->count() > 1;
                },
                'text' => __('texts.generic.menu.multigas'),
            ];
        }

        return $ret;
    }

    public static function dominantColor($obj)
    {
        $statuses = Status::orders();
        return $statuses[$obj->status]->color;
    }
}
