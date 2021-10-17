<?php

namespace App\View\Icons;

class Receipt extends IconsMap
{
    public static function commons($user)
    {
        return [
            'arrow-right' => (object) [
                'test' => function ($obj) {
                    return true;
                },
                'text' => _i('In Uscita'),
            ],
            'envelope' => (object) [
                'test' => function ($obj) {
                    return $obj->mailed;
                },
                'text' => _i('Inoltrata'),
            ],
        ];
    }
}
