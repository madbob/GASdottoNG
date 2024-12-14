<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

/**
    @property-read Booking|Gas|Invoice|Order|Supplier|null $target
 */
class Balance extends Model
{
    use Cachable;

    public function target(): MorphTo
    {
        // @phpstan-ignore-next-line
        return $this->morphTo()->withTrashed();
    }
}
