<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\GASModel;
use App\SluggableID;

/*
    Questa classe rappresenta un luogo di consegna
*/

class Delivery extends Model
{
    use GASModel, SluggableID;

    public $incrementing = false;

    public function users()
    {
        return $this->hasMany('App\User', 'preferred_delivery_id');
    }
}
