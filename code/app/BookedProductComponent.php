<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

class BookedProductComponent extends Model
{
    use GASModel, Cachable;

    public function variant(): BelongsTo
    {
        return $this->belongsTo('App\Variant');
    }

    public function value(): BelongsTo
    {
        return $this->belongsTo('App\VariantValue');
    }
}
