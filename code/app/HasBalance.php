<?php

namespace App;

use App\Balance;

trait HasBalance
{
	public function balances()
	{
		return $this->morphMany('App\Balance', 'target')->orderBy('date', 'desc');
	}

	public function lastBalance()
	{
		return $this->balances()->first();
	}

	public function updateBalance($date, $amount)
	{
		if (is_numeric($amount)) {
			$this->balances()->firstOrCreate(['date' => $date, Balance::fallbackColumn() => $amount]);
		}
		else {
			$amount['date'] = $date;
			$this->balances()->firstOrCreate($amount);
		}
	}
}
