<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Auth;

use App\GASModel;

class Aggregate extends Model
{
	use GASModel;

	private $names_string = null;
	private $dates_string = null;

	public function orders()
	{
		return $this->hasMany('App\Order')->with('products')->orderBy('end', 'desc');
	}

	public function getStatusAttribute()
	{
		$priority = ['suspended', 'open', 'closed', 'shipped', 'archived'];
		$index = 10;

		foreach ($this->orders as $order) {
			$a = array_search($order->status, $priority);
			if ($a < $index)
				$index = $a;
		}

		return $priority[$index];
	}

	public static function getByStatus($status, $inverse = false)
	{
		$operator = '=';
		if ($inverse)
			$operator = '!=';

		return Aggregate::whereHas('orders', function($query) use ($status, $operator) {
			$query->where('status', $operator, $status);
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
		$ret = $this->printableName();
		$icons = [];

		if ($this->userCan('supplier.orders'))
			$icons[] = 'th-list';
		if ($this->userCan('supplier.shippings'))
			$icons[] = 'arrow-down';

		switch($this->status) {
			case 'open':
				$icons[] = 'play';
				break;
			case 'suspended':
				$icons[] = 'pause';
				break;
			case 'closed':
				$icons[] = 'stop';
				break;
			case 'shipped':
				$icons[] = 'step-forward';
				break;
			case 'archived':
				$icons[] = 'eject';
				break;
		}

		if (!empty($icons)) {
			$ret .= '<div class="pull-right">';

			foreach ($icons as $i)
				$ret .= '<span class="glyphicon glyphicon-' . $i . '" aria-hidden="true"></span>&nbsp;';

			$ret .= '</div>';
		}

		$ret .= sprintf('<br/><small>%s</small>', $this->printableDates());
		return $ret;
	}

	public function userCan($action, $user = null)
	{
		if ($user == null)
			$user = Auth::user();

		foreach ($this->orders as $order)
			if ($order->supplier->userCan($action, $user))
				return true;

		return false;
	}

	public function getBookingsAttribute()
	{
		$ret = [];

		foreach ($this->orders as $order) {
			foreach ($order->bookings as $booking) {
				$user_id = $booking->user->id;
				if (!isset($ret[$user_id]))
					$ret[$user_id] = $booking->user;
			}
		}

		return $ret;
	}
}
