<?php

namespace App\View\Icons;

use App\Helpers\Status;

class Order extends IconsMap
{
    public static function commons($user)
    {
        $ret = [
            'card-list' => (object) [
                'test' => function ($obj) use ($user) {
                    return $user->can('supplier.orders', $obj);
                },
                'text' => _i("Puoi modificare l'ordine"),
            ],
            'arrow-down' => (object) [
                'test' => function ($obj) use ($user) {
                    return $user->can('supplier.shippings', $obj);
                },
                'text' => _i("Gestisci le consegne per l'ordine"),
            ],
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
