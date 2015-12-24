<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Lanz\Commentable\Commentable;
use App\GASModel;

class Product extends Model
{
	use Commentable, GASModel;

	public $incrementing = false;

	public function category()
	{
		return $this->belongsTo('App\Category');
	}

	public function measure()
	{
		return $this->belongsTo('App\Measure');
	}

	public function supplier()
	{
		return $this->belongsTo('App\Supplier');
	}

	public function variants()
	{
		return $this->hasMany('App\Variant')->orderBy('name', 'asc');
	}

	public function printablePrice()
	{
		if (!empty($this->transport) && $this->transport != 0)
			return sprintf('%.02f € + %.02f € trasporto', $this->price, $this->transport);
		else
			return sprintf('%.02f €', $this->price);
	}
}
