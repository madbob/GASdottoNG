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
                    return ($obj->keep_open_packages != 'no' && $obj->status == 'closed' && $obj->pendingPackages()->isEmpty() == false);
                },
                'text' => _i('Confezioni Da Completare'),
                'group' => 'status',
            ]
        ];

        foreach(Status::orders() as $identifier => $meta) {
            $ret[$meta->icon] = (object) [
                'test' => function ($obj) use ($identifier) {
                    return $obj->status == $identifier;
                },
                'text' => $meta->label,
                'group' => 'status',
            ];
        }

        return $ret;
    }
}
