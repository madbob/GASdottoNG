<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductPrice extends Model
{
	public $incrementing = false;

	public function product()
	{
		return $this->belongsTo('App\Product');
	}
}
