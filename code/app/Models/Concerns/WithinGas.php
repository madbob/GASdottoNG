<?php

/*
	Simile a HierarcableTrait, ma per i modelli che possono essere relazionati a
	molteplici GAS (solitamente in modo dinamico a seconda delle esplicite
	preferenze)
*/

namespace App\Models\Concerns;

use App\Scopes\RestrictedGAS;
use App\Gas;

trait WithinGas
{
	protected static function boot()
	{
		parent::boot();
		static::addGlobalScope(new RestrictedGAS());
	}

	public function gas()
    {
        return $this->belongsToMany(Gas::class);
    }

	public function guessGas()
	{
		$gas = Gas::all();

		if ($gas->count() == 1) {
			return $gas;
		}
		else {
			return null;
		}
	}
}
