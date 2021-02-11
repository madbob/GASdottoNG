<?php

namespace App;

use Illuminate\Http\Request;

trait HierarcableTrait
{
    public function gas()
    {
        return $this->belongsTo('App\Gas');
    }
}
