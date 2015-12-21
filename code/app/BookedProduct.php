<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\GASModel;

class BookedProduct extends Model
{
	use GASModel;
	
	public function product()
	{
		return $this->belongsTo('App\Product');
	}

	public function variants()
	{
		return $this->hasMany('App\BookedProductVariant');
	}
}
