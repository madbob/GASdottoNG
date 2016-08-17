<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
        public function gas()
        {
                $this->belongsTo('App\Gas');
        }
}
