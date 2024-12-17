<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\Concerns\TracksUpdater;
use App\Events\SluggableCreating;

class Group extends Model
{
    use GASModel, SluggableID, TracksUpdater;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $dispatchesEvents = [
        'creating' => SluggableCreating::class,
    ];

    protected static function boot()
    {
        parent::boot();
        static::initTrackingEvents();
    }

    public function circles(): HasMany
    {
        return $this->hasMany(Circle::class);
    }
}
