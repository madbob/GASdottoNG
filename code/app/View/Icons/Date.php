<?php

namespace App\View\Icons;

class Date extends IconsMap
{
    public static function commons($user)
    {
        return [
            'calendar-heart' => (object) [
                'test' => function ($obj) {
                    return true;
                },
                'text' => __('texts.notifications.calendar_date'),
            ],
        ];
    }
}
