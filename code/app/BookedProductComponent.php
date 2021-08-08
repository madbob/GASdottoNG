<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

class BookedProductComponent extends Model
{
    use GASModel, Cachable;

    public function variant()
    {
        return $this->belongsTo('App\Variant');
    }

    public function value()
    {
        return $this->belongsTo('App\VariantValue');
    }
}
