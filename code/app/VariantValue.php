<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\GASModel;
use App\SluggableID;

class VariantValue extends Model
{
	use GASModel, SluggableID;
	
	public $incrementing = false;

	public function getSlugID()
	{
		return sprintf('%s::%s', $this->variant_id, str_slug($this->value));
	}
}
