<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Lanz\Commentable\Commentable;
use App\GASModel;
use App\SluggableID;

class Product extends Model
{
	use Commentable, GASModel, SluggableID;

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

	public function orders()
	{
		return $this->belongsToMany('App\Order');
	}

	public function variants()
	{
		return $this->hasMany('App\Variant')->orderBy('name', 'asc');
	}

	public function getSlugID()
	{
		return sprintf('%s::%s::0', $this->supplier_id, str_slug($this->name));
	}

	public function nextId()
	{
		list($supplier, $name, $index) = explode('::', $this->id);
		return sprintf('%s::%s::%s', $supplier, $name, $index + 1);
	}

	public function printablePrice()
	{
		if (!empty($this->transport) && $this->transport != 0)
			return sprintf('%.02f € + %.02f € trasporto', $this->price, $this->transport);
		else
			return sprintf('%.02f €', $this->price);
	}
}
