<?php

namespace App;

trait CreditableTrait
{
    public function balances()
    {
        return $this->morphMany('App\Balance', 'target')->orderBy('date', 'desc');
    }

    private function fixFirstBalance()
    {
        $balance = new Balance();
        $balance->target_id = $this->id;
        $balance->target_type = get_class($this);
        $balance->bank = 0;
        $balance->cash = 0;
        $balance->suppliers = 0;
        $balance->deposits = 0;
        $balance->date = date('Y-m-d');
        $balance->save();
        return $balance;
    }

    public function getCurrentBalanceAmountAttribute()
    {
        $balance = $this->balances()->first();
        if ($balance == null)
            $balance = $this->fixFirstBalance();

        return $balance->bank + $balance->cash;
    }

    public function alterBalance($amount, $type = 'bank')
    {
        if (is_string($type)) {
            $type = [$type];
        }

        $balance = $this->balances()->first();
        if ($balance == null)
            $balance = $this->fixFirstBalance();

        foreach ($type as $t) {
            $balance->$t += $amount;
        }

        $balance->save();
    }
}
