<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use TeamTeaTime\Filer\AttachableTrait;

use App\AllowableTrait;
use App\GASModel;
use App\SluggableID;

class Gas extends Model
{
	use AttachableTrait, AllowableTrait, GASModel, SluggableID;

	public $incrementing = false;
}
