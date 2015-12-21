<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Lanz\Commentable\Commentable;
use TeamTeaTime\Filer\AttachableTrait;

use App\AllowableTrait;
use App\GASModel;

class Supplier extends Model
{
	use Commentable, AttachableTrait, AllowableTrait, GASModel;

	public function products()
	{
		return $this->hasMany('App\Product')->whereNotIn('id', function($query) {
			$query->select('previous_id')->from('products');
		})->orderBy('name');
	}

	public function orders()
	{
		return $this->hasMany('App\Order')->orderBy('end', 'desc');
	}

	public function contacts()
	{
		return $this->morphMany('App\Contact', 'target');
	}
}
