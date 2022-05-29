<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

class VatRate extends Model
{
    use HasFactory, GASModel, Cachable;

    protected $fillable = ['percentage', 'name'];

    public function products()
    {
        return $this->hasMany('App\Product');
    }
}
