<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Lanz\Commentable\Commentable;

use App\AttachableTrait;
use App\AllowableTrait;
use App\GASModel;
use App\SluggableID;

class Supplier extends Model
{
	use Commentable, AttachableTrait, AllowableTrait, GASModel, SluggableID;

	public $incrementing = false;

	public function products()
	{
		return $this->hasMany('App\Product')->where('active', '=', true)->whereNotIn('id', function($query) {
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

	public function printableHeader()
	{
		$ret = $this->printableName();
		$icons = $this->icons();

		if (!empty($icons)) {
			$ret .= '<div class="pull-right">';

			foreach ($icons as $i)
				$ret .= '<span class="glyphicon glyphicon-' . $i . '" aria-hidden="true"></span>&nbsp;';

			$ret .= '</div>';
		}

		return $ret;
	}

	protected function requiredAttachmentPermission()
	{
		return 'supplier.modify';
	}

	public function getDisplayURL()
	{
		return Illuminate\Routing\UrlGenerator::action('SuppliersController@show');
	}
}
