<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use TeamTeaTime\Filer\AttachableTrait;

use App\AllowableTrait;
use App\HasBalance;
use App\GASModel;
use App\SluggableID;

class Gas extends Model
{
	use AttachableTrait, AllowableTrait, HasBalance, GASModel, SluggableID;

	public $incrementing = false;
}
