<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Events\SluggableCreating;
use App\GASModel;
use App\SluggableID;
use App\Hierarchic;

class Category extends Model implements Hierarchic
{
    use HasFactory, GASModel, SluggableID;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $dispatchesEvents = [
        'creating' => SluggableCreating::class,
    ];

    public function children()
    {
        return $this->hasMany('App\Category', 'parent_id');
    }
}
