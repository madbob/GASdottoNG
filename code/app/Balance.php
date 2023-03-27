<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

class Balance extends Model
{
    use Cachable;

	public function target(): MorphTo
    {
		// @phpstan-ignore-next-line
        return $this->morphTo()->withTrashed();
    }
}
