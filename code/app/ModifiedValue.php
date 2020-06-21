<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ModifiedValue extends Model
{
    public function modifier()
    {
        return $this->belongsTo('App\Modifier');
    }
}
