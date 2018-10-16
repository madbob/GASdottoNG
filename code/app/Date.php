<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Date extends Model
{
    public function target()
    {
        return $this->morphTo();
    }

    public static function types()
    {
        return [
            [
                'label' => _i('Confermato'),
                'value' => 'confirmed'
            ],
            [
                'label' => _i('Provvisorio'),
                'value' => 'temp'
            ],
        ];
    }
}
