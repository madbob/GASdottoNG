<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

use App\Models\Concerns\HasChildren;
use App\Events\SluggableCreating;

class Category extends Model implements HasChildren
{
    use HasFactory, GASModel, SluggableID, Cachable;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['name'];

    protected $dispatchesEvents = [
        'creating' => SluggableCreating::class,
    ];

    public function children(): HasMany
    {
        return $this->hasMany('App\Category', 'parent_id');
    }

    public static function defaultValue()
    {
        return 'non-specificato';
    }
}
