<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

use App\Events\SluggableCreating;

class ModifierType extends Model
{
    use HasFactory, GASModel, SluggableID, Cachable;

    public $incrementing = false;

    protected $dispatchesEvents = [
        'creating' => SluggableCreating::class,
    ];

    protected $casts = [
        'classes' => 'array',
    ];

    public function modifiers()
    {
        return $this->hasMany('App\Modifier');
    }

    public static function byClass($class)
    {
        $ret = [];

        foreach(ModifierType::orderBy('name', 'asc')->get() as $modtype) {
            if (in_array($class, accessAttr($modtype, 'classes'))) {
                $ret[] = $modtype;
            }
        }

        return $ret;
    }
}
