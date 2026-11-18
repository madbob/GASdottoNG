<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

use App\Models\Concerns\SluggableID;

class Measure extends Model
{
    use Cachable, GASModel, HasFactory, SluggableID;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['name'];

    public function products(): HasMany
    {
        return $this->hasMany('App\Product')->orderBy('name', 'asc');
    }

    public static function defaultValue()
    {
        return 'non-specificato';
    }
}
