<?php

namespace App\View\Icons;

use Auth;

trait Status
{
    public static function statusIcons($ret)
    {
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
            'text' => _i('Cessato'),
        ];

        return $ret;
    }
}
