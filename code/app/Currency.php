<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

class Currency extends Model
{
    use GASModel, Cachable;

    public $incrementing = false;
    protected $keyType = 'string';

    public static function enabled()
    {
        return self::where('enabled', true)->orderBy('id', 'asc')->get();
    }

    public function printableName()
    {
        return $this->symbol;
    }
}
