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
		return $this->belongsToMany('App\Product')->with('measure')->with('category')->with('variants');
	}

	public function bookings()
	{
		return $this->hasMany('App\Booking')->with('user');
	}

	public function payment()
	{
		return $this->belongsTo('App\Movement');
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
			$b->status = 'pending';
			return $b;
		}
		else {
			return $ret;
		}
	}

	public function hasProduct($product)
	{
		foreach ($this->products as $p) {
			if ($p->id == $product->id)
				return true;
		}

		return false;
	}

	public function calculateSummary()
	{
		$summary = (object) [
			'price' => 0,
			'products' => []
		];

		$order = $this;
		$products = $order->supplier->products;
		$total_price = 0;
		$total_price_delivered = 0;
		$total_transport = 0;

		foreach($products as $product) {
			$q = BookedProduct::where('product_id', '=', $product->id)->whereHas('booking', function($query) use ($order) {
				$query->where('order_id', '=', $order->id);
			});

			$quantity = $q->sum('quantity');
			$delivered = $q->sum('delivered');
			$transport = $quantity * $product->transport;

			/*
				In presenza di varianti, devo calcolare la somma
				pezzo per pezzo essendoci di mezzo eventuali
				differenze di prezzo da valutare
			*/
			if ($product->variants->isEmpty()) {
				$price = $quantity * $product->price;
				$price_delivered = $delivered * $product->price;
			}
			else {
				$booked = $q->get();
				$price = 0;
				$price_delivered = 0;

				foreach($booked as $b) {
					$price += $b->quantityValue();
					$price_delivered += $b->deliveredValue();
				}
			}

			$summary->products[$product->id]['quantity'] = $quantity;
			$summary->products[$product->id]['price'] = $price;
			$summary->products[$product->id]['transport'] = $transport;
			$summary->products[$product->id]['delivered'] = $delivered;
			$summary->products[$product->id]['price_delivered'] = $price_delivered;

			$total_price += $price;
			$total_price_delivered += $price_delivered;
			$total_transport += $transport;

			$summary->products[$product->id]['notes'] = false;
			if ($product->package_size != 0 && $quantity != 0) {
				if ($product->portion_quantity <= 0)
					$test = $product->package_size;
				else
					$test = round($product->portion_quantity * $product->package_size, 2);

				$test = round($quantity % $test);
				if ($test != 0)
					$summary->products[$product->id]['notes'] = true;
			}
		}

		$summary->price = $total_price;
		$summary->price_delivered = $total_price_delivered;
		$summary->transport = $total_transport;
		return $summary;
	}
}
