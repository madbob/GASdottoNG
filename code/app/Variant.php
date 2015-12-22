<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\GASModel;

class Variant extends Model
{
	use GASModel;

	public $incrementing = false;

	public function values()
	{
		return $this->hasMany('App\VariantValue')->orderBy('value', 'asc');
	}
}
