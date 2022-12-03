<?php

/*
    Simili a WithinGas, ma per i modelli che sono relazionati ad un singolo GAS
*/

namespace App\Models\Concerns;

use Illuminate\Http\Request;

use App\Gas;

trait HierarcableTrait
{
    public function gas()
    {
        return $this->belongsTo(Gas::class);
    }
}
