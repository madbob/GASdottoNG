<?php

namespace App\View\Icons;

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
            'play' => (object) [
                'test' => function ($obj) {
                    return $obj->status == 'open';
                },
                'text' => _i('Prenotazioni Aperte'),
            ],
            'pause' => (object) [
                'test' => function ($obj) {
                    return $obj->status == 'suspended';
                },
                'text' => _i('In Sospeso'),
            ],
            'stop-fill' => (object) [
                'test' => function ($obj) {
                    return $obj->status == 'closed';
                },
                'text' => _i('Prenotazioni Chiuse'),
            ],
            'skip-forward' => (object) [
                'test' => function ($obj) {
                    return $obj->status == 'shipped';
                },
                'text' => _i('Consegnato'),
            ],
            'eject' => (object) [
                'test' => function ($obj) {
                    return $obj->status == 'archived';
                },
                'text' => _i('Archiviato'),
            ],
            'plus-circle' => (object) [
                'test' => function ($obj) {
                    return ($obj->status == 'closed' && $obj->hasPendingPackages());
                },
                'text' => _i('Confezioni Da Completare'),
            ]
        ];

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
