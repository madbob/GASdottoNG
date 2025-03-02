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
        return $this->belongsTo('App\Variant');
    }

    public function value(): BelongsTo
    {
        return $this->belongsTo('App\VariantValue');
    }

    /*************************************************************** GASModel */

    public function printableName()
    {
        return $this->value->printableName();
    }
}
