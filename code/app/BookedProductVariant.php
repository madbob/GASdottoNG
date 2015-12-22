<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\GASModel;

class BookedProductVariant extends Model
{
	use GASModel;

	public $incrementing = false;
}
