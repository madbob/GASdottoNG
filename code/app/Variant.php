<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\GASModel;
use App\SluggableID;

class Variant extends Model
{
	use GASModel, SluggableID;

	public $incrementing = false;

	public function product()
	{
		return $this->belongsTo('App\Product');
	}

	public function values()
	{
		return $this->hasMany('App\VariantValue')->orderBy('value', 'asc');
	}

	public function printableValues()
	{
		$vals = [];

		foreach ($this->values as $value) {
			$v = $value->value;
			if ($this->has_offset == true)
				$v .= sprintf(' (+%.02f€)', $value->price_offset);
			$vals[] = $v;
		}

		return join(', ', $vals);
	}

	public function getSlugID()
	{
		return sprintf('%s::%s', $this->product_id, str_slug($this->name));
	}
}
