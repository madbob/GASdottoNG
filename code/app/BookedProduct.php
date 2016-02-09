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

	private function fixQuantity($attribute)
	{
		$product = $this->product;

		$quantity = $this->$attribute;
		if ($product->partitioning != 0)
			$quantity = $this->$attribute * $product->partitioning;

		return ($product->price + $product->transport) * $quantity;
	}

	public function quantityValue()
	{
		return $this->fixQuantity('quantity');
	}

	public function deliveredValue()
	{
		return $this->fixQuantity('delivered');
	}
}
