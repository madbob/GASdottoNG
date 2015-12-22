<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\GASModel;

class Contact extends Model
{
	use GASModel;

	public $incrementing = false;

	public function target()
	{
		return $this->morphsTo();
	}
}
