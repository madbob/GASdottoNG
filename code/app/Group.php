<?php

/*
    Modello che implementa una aggregazione di utenti (chiamata pubblicamente
    "Aggregazione")
*/

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\Concerns\SluggableID;
use App\Models\Concerns\TracksUpdater;

class Group extends Model
{
    use GASModel, SluggableID, TracksUpdater;

    public $incrementing = false;

    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();
        static::initTrackingEvents();
    }

    public function circles(): HasMany
    {
        return $this->hasMany(Circle::class);
    }

    public function scopeForUsers($query)
    {
        $query->where('context', 'user');
    }

    public function scopeForBookings($query)
    {
        $query->where('context', 'booking');
    }
}
