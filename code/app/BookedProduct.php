<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\GASModel;
use App\SluggableID;

class BookedProduct extends Model
{
	use GASModel, SluggableID;

	public $incrementing = false;

	public function product()
	{
		return $this->belongsTo('App\Product');
	}

	public function booking()
	{
		return $this->belongsTo('App\Booking');
	}

	public function variants()
	{
		return $this->hasMany('App\BookedProductVariant', 'product_id');
	}

	public function getSlugID()
	{
		return sprintf('%s::%s', $this->booking->id, $this->product->id);
	}

	public function getValueAttribute()
	{
		$product = $this->product;

		$quantity = $this->quantity;
		if ($product->partitioning != 0)
			$quantity = $this->quantity * $product->partitioning;

		return $product->price * $quantity;
	}
}
