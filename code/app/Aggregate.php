<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\GASModel;

class Aggregate extends Model
{
	use GASModel;

	public $incrementing = false;
	private $names_string = null;
	private $dates_string = null;

	public function orders()
	{
		return $this->hasMany('App\Order')->orderBy('end', 'desc');
	}

	public static function getByStatus($status)
	{
		return Aggregate::whereHas('orders', function($query) use ($status) {
			$query->where('status', '=', $status);
		})->get();
	}

	private function computeStrings()
	{
		$names = [];
		$dates = [];

		foreach($this->orders as $order) {
			$names[] = $order->printableName();
			$dates[] = $order->printableDates();
		}

		$this->names_string = join(' / ', $names);
		$this->dates_string = join(' / ', $dates);
	}

	public function printableName()
	{
		if ($this->names_string == null)
			$this->computeStrings();

		return $this->names_string;
	}

	public function printableDates()
	{
		if ($this->dates_string == null)
			$this->computeStrings();

		return $this->dates_string;
	}

	public function printableHeader()
	{
		return sprintf('%s<br/><small>%s</small>', $this->printableName(), $this->printableDates());
	}
}
