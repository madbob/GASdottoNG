<?php

namespace App\View\Icons;

class Notification extends IconsMap
{
    public static function commons($user)
    {
        return [
            'highlighter' => (object) [
                'test' => function ($obj) {
                    return $obj->permanent;
                },
                'text' => __('texts.notifications.permanent_notification'),
            ],
        ];
    }
}
