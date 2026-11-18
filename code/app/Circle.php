<?php

/*
    Modello che implementa un elemento di una Aggregazione (chiamata
    pubblicamente "Gruppo")
*/

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\Concerns\SluggableID;
use App\Models\Concerns\TracksUpdater;
use App\Models\Concerns\ModifiableTrait;
use App\Parameters\ModifierTarget\Circle as CircleModifier;

class Circle extends Model
{
    use GASModel, ModifiableTrait, SluggableID, TracksUpdater;

    public $incrementing = false;

    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();
        static::initTrackingEvents();
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    /******************************************************** ModifiableTrait */

    public function getModifierDefinition()
    {
        return new CircleModifier();
    }
}
