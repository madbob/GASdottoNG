<?php

namespace App\View\Icons;

class Receipt extends IconsMap
{
    public static function commons($user)
    {
        return [
            'envelope' => (object) [
                'test' => function ($obj) {
                    return $obj->mailed;
                },
                'text' => _i('Inoltrata'),
            ],
        ];
    }
}
