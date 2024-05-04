<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\Concerns\TracksUpdater;
use App\Models\Concerns\ModifiableTrait;
use App\Events\SluggableCreating;

class Circle extends Model
{
    use GASModel, SluggableID, TracksUpdater, ModifiableTrait;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $dispatchesEvents = [
        'creating' => SluggableCreating::class
    ];

    protected static function boot()
    {
        parent::boot();
        static::initTrackingEvents();
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}
