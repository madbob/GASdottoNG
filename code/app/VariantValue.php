<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

use App\Models\Concerns\SluggableID;

class VariantValue extends Model
{
    use Cachable, GASModel, SluggableID;

    public $incrementing = false;

    protected $keyType = 'string';

    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class);
    }

    public function booked(): HasMany
    {
        return $this->hasMany(BookedProductComponent::class, 'value_id');
    }

    public function hasBookings()
    {
        return $this->booked()->count() != 0;
    }

    /************************************************************ SluggableID */

    protected function getSlugParts(): ?array
    {
        return [$this->variant_id, Str::slug($this->value)];
    }

    /*************************************************************** GASModel */

    public function printableName()
    {
        return $this->value;
    }
}
