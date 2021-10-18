<?php

namespace App\View\Icons;

class Attachment extends IconsMap
{
    public static function commons($user)
    {
        return [
            'image' => (object) [
                'test' => function ($obj) {
                    return $obj->isImage();
                },
                'text' => _i('Immagine'),
            ],
            'shield-x' => (object) [
                'test' => function ($obj) {
                    return ($obj->users()->count() != 0);
                },
                'text' => _i('Accesso limitato'),
            ],
        ];
    }
}
