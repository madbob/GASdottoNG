<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\GASModel;

class BookedProductComponent extends Model
{
	use GASModel;

	public function variant()
	{
		return $this->belongsTo('App\Variant');
	}

        public function value()
	{
		return $this->belongsTo('App\VariantValue');
	}
}
