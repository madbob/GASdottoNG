<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use GASModel;

    public static function enabled()
    {
        return self::where('enabled', true)->orderBy('id', 'asc')->get();
    }

    public function printableName()
    {
        return $this->symbol;
    }
}
