<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\GASModel;
use App\SluggableID;

class Order extends Model
{
	use GASModel, SluggableID;

	public $incrementing = false;

	public function supplier()
	{
		return $this->belongsTo('App\Supplier');
	}

	public function products()
	{
		return $this->belongsToMany('App\Product');
	}

	public function bookings()
	{
		return $this->hasMany('App\Booking')->with('user', function($query) {
			$query->orderBy('surname', 'asc');
		});
	}

	public function getSlugID()
	{
		return sprintf('%s::%s', $this->supplier->id, str_slug(strftime('%d %B %G', strtotime($this->start))));
	}

	public function printableName()
	{
		return $this->supplier->name;
	}

	public function printableDates()
	{
		$start = strtotime($this->start);
		$end = strtotime($this->end);
		$string = sprintf('da %s a %s', strftime('%A %d %B %G', $start), strftime('%A %d %B %G', $end));
		if ($this->shipping != null && $this->shipping != '0000-00-00') {
			$shipping = strtotime($this->shipping);
			$string .= sprintf (', in consegna %s', strftime('%A %d %B %G', $shipping));
		}

		return $string;
	}

	public function userBooking($userid = null)
	{
		if ($userid == null)
			$userid = Auth::user()->id;

		$ret = $this->hasMany('App\Booking')->whereHas('user', function($query) use ($userid) {
			$query->where('id', '=', $userid);
		})->first();

		if ($ret == null) {
			$b = new Booking;
			$b->user_id = $userid;
			$b->order_id = $this->id;
			return $b;
		}
		else {
			return $ret;
		}
	}
}
