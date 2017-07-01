<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\GASModel;

class VatRate extends Model
{
    use GASModel;

    public function products()
    {
        return $this->hasMany('App\Product');
    }
}
