<?php

namespace App\View\Icons;

class Booking extends IconsMap
{
    public static function commons($user)
    {
        return [
            'clock' => (object) [
                'test' => function ($obj) {
                    return $obj->status != 'shipped';
                },
                'text' => _i('Da consegnare'),
            ],
            'check' => (object) [
                'test' => function ($obj) {
                    return $obj->status == 'shipped';
                },
                'text' => _i('Consegnato'),
            ],
            'save' => (object) [
                'test' => function ($obj) {
                    return $obj->status == 'saved';
                },
                'text' => _i('Salvato'),
            ],
        ];
    }
}
