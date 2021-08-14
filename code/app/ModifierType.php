<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

use App\Events\SluggableCreating;

class ModifierType extends Model
{
    use GASModel, SluggableID, Cachable;

    public $incrementing = false;

    protected $dispatchesEvents = [
        'creating' => SluggableCreating::class,
    ];

    protected $casts = [
        'classes' => 'array',
    ];

    public function modifiers()
    {
        return $this->hasMany('App\Modifier');
    }
}
