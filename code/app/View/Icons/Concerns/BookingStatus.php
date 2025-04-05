<?php

namespace App\View\Icons\Concerns;

trait BookingStatus
{
    public static function bookingStatusIcons($ret)
    {
        $ret['clock'] = (object) [
            'test' => function ($obj) {
                return $obj->status != 'shipped';
            },
            'text' => _i('Da consegnare'),
        ];

        $ret['check'] = (object) [
            'test' => function ($obj) {
                return $obj->status == 'shipped';
            },
            'text' => _i('Consegnato'),
        ];

        $ret['save'] = (object) [
            'test' => function ($obj) {
                return $obj->status == 'saved';
            },
            'text' => _i('Salvato'),
        ];

        return $ret;
    }
}
