<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\GASModel;
use App\SluggableID;

class BookedProductVariant extends Model
{
	use GASModel, SluggableID;

	public $incrementing = false;

	public function product()
	{
		return $this->belongsTo('App\BookedProduct');
	}

	public function variant()
	{
		return $this->belongsTo('App\Variant');
	}

	public function getSlugID()
	{
		return sprintf('%s::%s', $this->product->id, $this->variant->id);
	}
}
