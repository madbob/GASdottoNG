<?php

namespace App\View\Icons;

trait Status
{
    public static function statusIcons($ret)
    {
        $ret['person-x'] = (object) [
            'test' => function ($obj) {
                return ! is_null($obj->deleted_at) && ! is_null($obj->suspended_at);
            },
            'text' => _i('Rimosso'),
        ];
        
        $ret['hand-thumbs-down'] = (object) [
            'test' => function ($obj) {
                return ! is_null($obj->suspended_at);
            },
            'text' => _i('Sospeso'),
        ];

        $ret['slash-circle'] = (object) [
            'test' => function ($obj) {
                return ! is_null($obj->deleted_at);
            },
            'text' => _i('Cessato'),
        ];

        return $ret;
    }
}
