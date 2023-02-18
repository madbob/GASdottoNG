<?php

namespace App\View\Icons;

use App\Helpers\Status;
use App\Gas;

class Aggregate extends IconsMap
{
    public static function commons($user)
    {
        $ret = [
            'card-list' => (object) [
                'test' => function ($obj) use ($user) {
                    return $user->can('supplier.orders', $obj);
                },
                'text' => _i('Puoi modificare'),
            ],
            'arrow-down' => (object) [
                'test' => function ($obj) use ($user) {
                    return $user->can('supplier.shippings', $obj);
                },
                'text' => _i('Gestisci le consegne'),
            ],
            'plus-circle' => (object) [
                'test' => function ($obj) {
                    return ($obj->status == 'closed' && $obj->hasPendingPackages());
                },
                'text' => _i('Confezioni Da Completare'),
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

        if (Gas::count() > 1) {
            $ret['share'] = (object) [
                'test' => function ($obj) {
                    return $obj->gas()->count() > 1;
                },
                'text' => _i('Multi-GAS'),
            ];
        }

        return $ret;
    }
}
