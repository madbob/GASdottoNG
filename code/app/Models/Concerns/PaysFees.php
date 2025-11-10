<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

use Carbon\Carbon;

use App\Movement;

trait PaysFees
{
    public function fee(): BelongsTo
    {
        return $this->belongsTo(Movement::class);
    }

    public function deposit(): BelongsTo
    {
        return $this->belongsTo(Movement::class);
    }

    /*
        Per recuperare l'ultima quota pagata.
        La quota corrente e attiva è direttamente relazionata all'istanza per
        tramite dell'attributo fee_id e della relazione fee(), per trovare
        l'ultima quota pagata (laddove non sia più attiva) devo attingere
        direttamente dalla tabella dei Movement
    */
    public function latestFee()
    {
        return $this->hasOne(Movement::class, 'sender_id', 'id')
            ->where('type', 'annual-fee')
            ->orderBy('date', 'desc');
    }

    public function expiredFee(): bool
    {
        if ($this->fee) {
            $expiration = $this->gas->getConfig('year_closing');
            $previous_expiration = Carbon::parse($expiration)->subYears(1);
            $actual_expiration = Carbon::parse($this->fee->date);
            return $actual_expiration->lessThan($previous_expiration);
        }

        return true;
    }
}
