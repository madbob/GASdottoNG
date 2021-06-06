<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Events\SluggableCreating;
use App\GASModel;
use App\SluggableID;

class Measure extends Model
{
    use HasFactory, GASModel, SluggableID;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $dispatchesEvents = [
        'creating' => SluggableCreating::class,
    ];

    public function products()
    {
        return $this->hasMany('App\Product')->orderBy('name', 'asc');
    }
}
