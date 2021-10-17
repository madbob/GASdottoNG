<?php

namespace App\View\Icons;

class Order extends IconsMap
{
    public static function commons($user)
    {
        return [
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
            'play' => (object) [
                'test' => function ($obj) {
                    return $obj->status == 'open';
                },
                'text' => _i('Prenotazioni Aperte'),
                'group' => 'status',
            ],
            'pause' => (object) [
                'test' => function ($obj) {
                    return $obj->status == 'suspended';
                },
                'text' => _i('In Sospeso'),
                'group' => 'status',
            ],
            'stop-fill' => (object) [
                'test' => function ($obj) {
                    return $obj->status == 'closed';
                },
                'text' => _i('Prenotazioni Chiuse'),
                'group' => 'status',
            ],
            'skip-forward' => (object) [
                'test' => function ($obj) {
                    return $obj->status == 'shipped';
                },
                'text' => _i('Consegnato'),
                'group' => 'status',
            ],
            'eject' => (object) [
                'test' => function ($obj) {
                    return $obj->status == 'archived';
                },
                'text' => _i('Archiviato'),
                'group' => 'status',
            ],
            'plus-circle' => (object) [
                'test' => function ($obj) {
                    return ($obj->keep_open_packages != 'no' && $obj->status == 'closed' && $obj->pendingPackages()->isEmpty() == false);
                },
                'text' => _i('Confezioni Da Completare'),
                'group' => 'status',
            ]
        ];
    }
}
