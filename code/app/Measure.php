<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\Events\SluggableCreating;
use App\GASModel;
use App\SluggableID;

class Measure extends Model
{
    use GASModel, SluggableID;

    public $incrementing = false;

    protected $dispatchesEvents = [
        'creating' => SluggableCreating::class,
    ];

    public function products()
    {
        return $this->hasMany('App\Product')->orderBy('name', 'asc');
    }
}
