<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\GASModel;

class Booking extends Model
{
	use GASModel;

	public function user()
	{
		return $this->belongsTo('App\User');
	}

	public function products()
	{
		return $this->hasMany('App\BookedProduct')->whereHas('product', function($query) {
			$query->orderBy('name', 'asc');
		});
	}

	public function getBooked($product)
	{
		$p = $this->products()->whereHas('product', function($query) use ($product) {
			$query->where('id', '=', $product->id);
		})->first();

		if ($p == null)
			return 0;
		else
			return $p->quantity;
	}

	public function getValueAttribute()
	{
		$sum = 0;

		foreach($this->products as $booked)
			$sum += $booked->product->price * $booked->quantity;

		return $sum;
	}

	public function getDeliveredAttribute()
	{
		$sum = 0;

		foreach($this->products as $booked)
			$sum += $booked->product->price * $booked->delivered;

		return $sum;
	}
}
