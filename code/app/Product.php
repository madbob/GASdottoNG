<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Lanz\Commentable\Commentable;
use App\GASModel;

class Product extends Model
{
	use Commentable, GASModel;

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

	public function prices()
	{
		return $this->hasMany('App\ProductPrice')->orderBy('quantity', 'asc');
	}

	public function variants()
	{
		return $this->hasMany('App\Variant')->orderBy('name', 'asc');
	}

	public function printablePrice($order = null)
	{
		$prices = $this->prices;
		if ($prices->count() == 1) {
			$p = $prices->first();
			return sprintf('%.02f € + %.02f € trasporto', $p->price, $p->transport);
		}
		else {
			return 'TODO';
		}
	}
}
