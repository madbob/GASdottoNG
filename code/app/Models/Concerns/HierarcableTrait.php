<?php

/*
    Simili a WithinGas, ma per i modelli che sono relazionati ad un singolo GAS
*/

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Gas;

trait HierarcableTrait
{
    public function gas(): BelongsTo
    {
        return $this->belongsTo(Gas::class);
    }
}
