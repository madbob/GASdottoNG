<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use TeamTeaTime\Filer\AttachableTrait;

use App\AllowableTrait;
use App\GASModel;

class Gas extends Model
{
	use AttachableTrait, AllowableTrait, GASModel;
}
