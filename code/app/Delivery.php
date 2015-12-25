<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\GASModel;
use App\SluggableID;

class Delivery extends Model
{
	use GASModel, SluggableID;

	public $incrementing = false;
}
