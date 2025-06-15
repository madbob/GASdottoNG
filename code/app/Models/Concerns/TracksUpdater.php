<?php

/*
    Usato dai modelli che tracciano data e operatore dell'ultima modifica
    effettuata.
    I form primari di gestione delle istanze delle classi che usano questo trait
    sono processati con la funzione formatUpdater() per generare la nota "Ultima
    Modifica" che appare al fondo di essi.
    Tutti i modelli che usano questo tratto devono avere una colonna updated_by
    nello schema del DB
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
            return __('generic.updated_at_formatted', [
                'date' => $this->updated_at->format('d/m/Y'),
                'person' => $this->updater->printableName(),
            ]);
        }
        else {
            return '';
        }
    }

    private static function updateUser($model): void
    {
        if ($model->isDirty('updated_by') === false) {
            $user = Auth::user();
            if ($user) {
                $model->updated_by = $user->id;
            }
        }
    }

    /*
        Questa funzione va invocata nel method boot() del Model, ed inizializza
        gli eventi che permettono di aggiornare automaticamente l'informazione
        sull'utente che ha modificato per l'ultima volta l'entità
    */
    protected static function initTrackingEvents(): void
    {
        static::creating(fn ($model) => self::updateUser($model));
        static::updating(fn ($model) => self::updateUser($model));
    }
}
