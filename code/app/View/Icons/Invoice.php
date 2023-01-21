<?php

namespace App\View\Icons;

class Invoice extends IconsMap
{
    public static function commons($user)
    {
        $ret = [
            'clock' => (object) [
                'test' => function ($obj) {
                    return $obj->status == 'pending';
                },
                'text' => _i('In Attesa'),
            ],
            'pin-angle' => (object) [
                'test' => function ($obj) {
                    return $obj->status == 'to_verify';
                },
                'text' => _i('Da Verificare'),
            ],
            'search' => (object) [
                'test' => function ($obj) {
                    return $obj->status == 'verified';
                },
                'text' => _i('Verificata'),
            ],
            'check' => (object) [
                'test' => function ($obj) {
                    return $obj->status == 'payed';
                },
                'text' => _i('Pagata'),
            ],
        ];

        return $ret;
    }
}
