<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Date extends Model
{
    public static function types()
    {
        return [
            [
                'label' => _i('Confermato'),
                'value' => 'confirmed'
            ],
            [
                'label' => _i('Temporaneo'),
                'value' => 'temp'
            ],
        ];
    }
}
