<?php

namespace app;

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
}
