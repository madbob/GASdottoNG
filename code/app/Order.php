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

	public function aggregate()
	{
		return $this->belongsTo('App\Aggregate');
	}

	public function products()
	{
		return $this->belongsToMany('App\Product');
	}

	public function bookings()
	{
		return $this->hasMany('App\Booking');
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

	public function calculateSummary()
	{
		$summary = (object) [
			'price' => 0,
			'products' => []
		];

		$order = $this;
		$products = $order->products;
		$total_price = 0;
		$total_transport = 0;

		foreach($products as $product) {
			$q = BookedProduct::where('product_id', '=', $product->id)->whereHas('booking', function($query) use ($order) {
				$query->where('order_id', '=', $order->id);
			});

			$quantity = $q->sum('quantity');
			$delivered = $q->sum('delivered');

			$price = $quantity * $product->price;
			$summary->products[$product->id]['price'] = $price;
			$total_price += $price;

			$transport = $quantity * $product->transport;
			$summary->products[$product->id]['transport'] = $transport;
			$total_transport += $transport;

			$summary->products[$product->id]['quantity'] = $quantity;
			$summary->products[$product->id]['delivered'] = $delivered;
			$summary->products[$product->id]['notes'] = '';
		}

		$summary->price = $total_price;
		$summary->transport = $total_transport;
		return $summary;
	}
}
