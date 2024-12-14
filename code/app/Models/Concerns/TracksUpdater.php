<?php

/*
    Usato dai modelli che tracciano data e operatore dell'ultima modifica
    effettuata.
    I form primari di gestione delle istanze delle classi che usano questo trait
    sono processati con la funzione formatUpdater() per generare la nota "Ultima
    Modifica" che appare al fondo di essi
*/

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

use App\User;

trait TracksUpdater
{
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getPrintableUpdaterAttribute(): string
    {
        if ($this->updater) {
            return _i('Ultima Modifica: <br class="d-block d-md-none">%s - %s', [$this->updated_at->format('d/m/Y'), $this->updater->printableName()]);
        }
        else {
            return '';
        }
    }

    private static function updateUser($model): void
    {
        if ($model->isDirty('updated_by') == false) {
            $user = Auth::user();
            if ($user) {
                $model->updated_by = $user->id;
            }
        }
    }

    protected static function initTrackingEvents(): void
    {
        static::creating(fn ($model) => self::updateUser($model));
        static::updating(fn ($model) => self::updateUser($model));
    }
}
