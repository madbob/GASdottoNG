<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

use App\Events\SluggableCreating;

class Variant extends Model
{
    use Cachable, GASModel, SluggableID;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $dispatchesEvents = [
        'creating' => SluggableCreating::class,
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(VariantValue::class)->orderBy('value', 'asc');
    }

    public function printableValues()
    {
        return $this->values->pluck('value')->join(', ');
    }

    public function getSlugID()
    {
        return sprintf('%s::%s', $this->product_id, Str::slug(substr($this->name, 0, 50)));
    }
}
