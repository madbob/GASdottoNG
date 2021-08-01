<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

use Auth;

use App\Scopes\RestrictedGAS;
use App\Events\SluggableCreating;
use App\Events\AttachableToGas;

/*
    Questa classe rappresenta un luogo di consegna
*/

class Delivery extends Model
{
    use HasFactory, ModifiableTrait, GASModel, SluggableID, Cachable;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $dispatchesEvents = [
        'creating' => SluggableCreating::class,
        'created' => AttachableToGas::class
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new RestrictedGAS());
    }

    public function gas()
    {
        return $this->belongsToMany('App\Gas');
    }

    public function users()
    {
        return $this->hasMany('App\User', 'preferred_delivery_id');
    }
}
