<?php

namespace App\View\Icons;

class Supplier extends IconsMap
{
    private static function altIcons($ret, $user)
    {
        if ($user->can('supplier.add', $user->gas)) {
            $ret['hand-thumbs-down'] = (object) [
                'test' => function ($obj) {
                    return !is_null($obj->suspended_at);
                },
                'text' => _i('Sospeso'),
            ];

            $ret['slash-circle'] = (object) [
                'test' => function ($obj) {
                    return !is_null($obj->deleted_at);
                },
                'text' => _i('Eliminato'),
            ];
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
                'text' => _i('Puoi modificare il fornitore'),
            ],
            'card-list' => (object) [
                'test' => function ($obj) use ($user) {
                    return $user->can('supplier.orders', $obj);
                },
                'text' => _i('Puoi aprire nuovi ordini per il fornitore'),
            ],
            'arrow-down' => (object) [
                'test' => function ($obj) use ($user) {
                    return $user->can('supplier.shippings', $obj);
                },
                'text' => _i('Gestisci le consegne per il fornitore'),
            ],
        ];

        return self::altIcons($ret, $user);
    }
}
