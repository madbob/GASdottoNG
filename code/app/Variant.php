<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

use App\Models\Concerns\SluggableID;

class Variant extends Model
{
    use Cachable, GASModel, SluggableID;

    public $incrementing = false;

    protected $keyType = 'string';

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(VariantValue::class)->orderBy('sorting', 'asc');
    }

    public function booked(): HasMany
    {
        return $this->hasMany(BookedProductComponent::class);
    }

    public function hasBookings()
    {
        return $this->booked()->count() != 0;
    }

    public function printableValues()
    {
        return $this->values->pluck('value')->join(', ');
    }

    /************************************************************ SluggableID */

    protected function getSlugParts(): ?array
    {
        return [$this->product_id, Str::slug($this->name)];
    }
}
