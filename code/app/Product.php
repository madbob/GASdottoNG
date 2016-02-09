<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Lanz\Commentable\Commentable;
use App\GASModel;
use App\SluggableID;

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
		return $this->hasMany('App\Variant')->orderBy('name', 'asc');
	}

	public function getSlugID()
	{
		return sprintf('%s::%s::0', $this->supplier_id, str_slug($this->name));
	}

	/*
		Se il prodotto è incluso all'interno di almeno un ordine
		(eventualmente != $order, se definito), esso viene duplicato e
		la copia viene ritornata.
		Da invocare quando un prodotto sta per essere modificato, per
		preservare la copia precedentemente assegnata ad un ordine
	*/
	public function nextChain($order = null)
	{
		$p = $this;

		$query = Order::whereHas('products', function($query) use ($p) {
			$query->where('id', $p->id);
		});

		if ($order != null)
			$query->where('id', '!=', $order->id);

		$master_order = $query->first();

		if ($master_order != null) {
			$new_p = $p->replicate();
			$new_p->id = $p->nextId();
			$new_p->previous_id = $p->id;
			$new_p->save();

			foreach($p->variants as $variant) {
				$new_var = new Variant();
				$new_var->name = $variant->name;
				$new_var->has_offset = $variant->has_offset;
				$new_var->product_id = $new_p->id;
				$new_var->save();

				foreach($variant->values as $value) {
					$new_val = new VariantValue();
					$new_val->value = $value->value();
					$new_val->price_offset = $value->price_offset;
					$new_val->variant_id = $new_var->id;
					$new_val->save();
				}
			}

			return $new_p;
		}

		return $this;
	}

	public function nextId()
	{
		list($supplier, $name, $index) = explode('::', $this->id);

		do {
			$index += 1;
			$ret = sprintf('%s::%s::%s', $supplier, $name, $index);
			$test = Product::find($ret);
		} while($test != null);

		return $ret;
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
		if ($this->partitioning != 0) {
			return sprintf('%.02f %s', $this->partitioning, $this->measure->name);
		}
		else {
			$m = $this->measure;
			if ($m == null)
				return '';
			else
				return $m->name;
		}
	}

	public function printableDetails()
	{
		$details = [];

		if ($this->minimum != 0)
			$details[] = sprintf('Minimo: %s', $this->minimum);
		if ($this->maximum != 0)
			$details[] = sprintf('Massimo: %s', $this->maximum);
		if ($this->multiple != 0)
			$details[] = sprintf('Multiplo: %s', $this->multiple);

		return join(', ', $details);
	}
}
