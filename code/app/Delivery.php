<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

use Auth;

use App\Scopes\RestrictedGAS;
use App\Events\SluggableCreating;
use App\Events\AttachableToGas;

/*
    Questa classe rappresenta un luogo di consegna
*/

class Delivery extends Model
{
    use ModifiableTrait, GASModel, SluggableID;

    public $incrementing = false;

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
