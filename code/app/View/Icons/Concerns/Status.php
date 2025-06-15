<?php

namespace App\View\Icons\Concerns;

trait Status
{
    public static function statusIcons($ret)
    {
        $ret['hand-thumbs-down'] = (object) [
            'test' => function ($obj) {
                return ! is_null($obj->suspended_at);
            },
            'text' => __('user.statuses.suspended'),
        ];

        $ret['slash-circle'] = (object) [
            'test' => function ($obj) {
                return ! is_null($obj->deleted_at);
            },
            'text' => __('user.statuses.deleted'),
        ];

        return $ret;
    }
}
