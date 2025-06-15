<?php

namespace App\View\Icons;

use App\Helpers\Status;
use App\Gas;

class Aggregate extends IconsMap
{
    public static function commons($user)
    {
        static $gas_count = null;

        $ret = [
            'plus-circle' => (object) [
                'test' => function ($obj) {
                    return $obj->status == 'closed' && $obj->hasPendingPackages();
                },
                'text' => __('orders.pending_packages'),
            ],
        ];

        foreach (Status::orders() as $identifier => $meta) {
            $ret[$meta->icon] = (object) [
                'test' => function ($obj) use ($identifier) {
                    return $obj->status == $identifier;
                },
                'text' => $meta->label,
                'group' => 'status',
            ];
        }

        if (is_null($gas_count)) {
            $gas_count = Gas::count();
        }

        if ($gas_count > 1) {
            $ret['share'] = (object) [
                'test' => function ($obj) {
                    return $obj->gas()->count() > 1;
                },
                'text' => __('generic.menu.multigas'),
            ];
        }

        return $ret;
    }
}
