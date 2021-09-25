<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

class VatRate extends Model
{
    use GASModel, Cachable;

    protected $fillable = ['percentage', 'name'];

    public function products()
    {
        return $this->hasMany('App\Product');
    }
}
