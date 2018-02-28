<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

use Auth;

use App\Events\SluggableCreating;
use App\Events\AttachableToGas;
use App\GASModel;
use App\SluggableID;

/*
    Questa classe rappresenta un luogo di consegna
*/

class Delivery extends Model
{
    use GASModel, SluggableID;

    public $incrementing = false;

    protected $events = [
        'creating' => SluggableCreating::class,
        'created' => AttachableToGas::class
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('gas', function (Builder $builder) {
            $builder->whereHas('gas', function($query) {
                $user = Auth::user();
                if ($user == null)
                    return;
                $query->where('gas_id', $user->gas->id);
            });
        });
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
