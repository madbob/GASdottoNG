<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Lanz\Commentable\Commentable;
use App\GASModel;
use App\SluggableID;
use App\Booking;
use App\BookedProduct;

class Product extends Model
{
	use Commentable, GASModel, SluggableID;

	public $incrementing = false;

	public function category()
	{
		return $this->belongsTo('App\Category');
	}

	public function measure()
	{
		return $this->belongsTo('App\Measure');
	}

	public function supplier()
	{
		return $this->belongsTo('App\Supplier');
	}

	public function orders()
	{
		return $this->belongsToMany('App\Order');
	}

	public function variants()
	{
		return $this->hasMany('App\Variant')->with('values')->orderBy('name', 'asc');
	}

	public function getSlugID()
	{
		return sprintf('%s::%s', $this->supplier_id, str_slug($this->name));
	}

	public function stillAvailable($order)
	{
		if ($this->max_available == 0)
			return 0;

		$quantity = BookedProduct::where('product_id', '=', $this->id)->whereHas('booking', function($query) use ($order) {
			$query->where('order_id', '=', $order->id);
		})->sum('quantity');

		return $this->max_available - $quantity;
	}

	public function bookingsInOrder($order)
	{
		$id = $this->id;

		return Booking::where('order_id', '=', $order->id)->whereHas('products', function($query) use ($id) {
			$query->where('product_id', '=', $id);
		})->get();
	}

	public function printablePrice()
	{
		if (!empty($this->transport) && $this->transport != 0)
			$str = sprintf('%.02f € / %s + %.02f € trasporto', $this->price, $this->measure->name, $this->transport);
		else
			$str = sprintf('%.02f € / %s', $this->price, $this->measure->name);

		if ($this->variable)
			$str .= '<small> (prodotto a prezzo variabile)</small>';

		return $str;
	}

	public function printableMeasure()
	{
		if ($this->portion_quantity != 0) {
			return sprintf('%.02f %s', $this->portion_quantity, $this->measure->name);
		}
		else {
			$m = $this->measure;
			if ($m == null)
				return '';
			else
				return $m->name;
		}
	}

	public function printableDetails($order)
	{
		$details = [];

		if ($this->min_quantity != 0)
			$details[] = sprintf('Minimo: %.02f', $this->min_quantity);
		if ($this->max_quantity != 0)
			$details[] = sprintf('Massimo: %.02f', $this->max_quantity);
		if ($this->max_available != 0)
			$details[] = sprintf('Disponibile: %.02f (%.02f totale)', $this->stillAvailable($order), $this->max_available);
		if ($this->multiple != 0)
			$details[] = sprintf('Multiplo: %.02f', $this->multiple);

		return join(', ', $details);
	}
}
