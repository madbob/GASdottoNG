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
                    return $obj->keep_open_packages != 'no' && $obj->status == 'closed' && $obj->pendingPackages()->isEmpty() == false;
                },
                'text' => _i('Confezioni Da Completare'),
                'group' => 'status',
            ],
        ];

        $ret = self::unrollStatuses($ret, Status::orders());

        return $ret;
    }
}
