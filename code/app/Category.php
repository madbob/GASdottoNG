<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

use App\Models\Concerns\HasChildren;
use App\Events\SluggableCreating;

class Category extends Model implements HasChildren
{
    use Cachable, GASModel, HasFactory, SluggableID;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['name'];

    protected $dispatchesEvents = [
        'creating' => SluggableCreating::class,
    ];

    public function parent(): BelongsTo
    {
        return $this->BelongsTo(Category::class);
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public static function defaultValue()
    {
        return 'non-specificato';
    }
}
