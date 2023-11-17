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

    public function expiredFee(): bool
    {
        if ($this->fee) {
            $expiration = $this->gas->getConfig('year_closing');
            $previous_expiration = Carbon::parse($expiration)->subYears(1);
            $actual_expiration = Carbon::parse($this->fee->date);

            if ($actual_expiration->lessThan($previous_expiration)) {
                return true;
            }
            else {
                return false;
            }
        }

        return true;
    }
}
