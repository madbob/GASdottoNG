<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

class BookedProductComponent extends Model
{
    use Cachable, GASModel;

    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class);
    }

    public function value(): BelongsTo
    {
        return $this->belongsTo(VariantValue::class);
    }

    /*************************************************************** GASModel */

    public function printableName()
    {
        return $this->value->printableName();
    }
}
