<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\GASModel;

class Contact extends Model
{
	use GASModel;
	
	public function target()
	{
		return $this->morphsTo();
	}
}
