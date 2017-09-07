<?php

namespace App;

use DB;

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
        $balance->gas = 0;
        $balance->suppliers = 0;
        $balance->deposits = 0;
        $balance->current = true;
        $balance->date = date('Y-m-d');
        $balance->save();
        return $balance;
    }

    public function resetCurrentBalance()
    {
        $this->current_balance->delete();

        if ($this->balances()->count() == 0) {
            $this->fixFirstBalance();
        }
        else {
            $latest = $this->balances()->where('current', false)->first();
            $new = $latest->replicate();

            $new->date = date('Y-m-d G:i:s');
            $new->current = true;
            $new->save();
        }
    }

    public static function acceptedClasses()
    {
        $ret = [];

        $models = modelsUsingTrait('App\CreditableTrait');
        foreach($models as $m)
            $ret[$m] = $m::commonClassName();

        return $ret;
    }

    public static function resetAllCurrentBalances()
    {
        $classes = DB::table('balances')->select('target_type')->distinct()->get();
        foreach($classes as $c) {
            $class = $c->target_type;
            $objects = $class::all();
            foreach($objects as $obj)
                $obj->resetCurrentBalance();
        }
    }

    public static function duplicateAllCurrentBalances($latest_date)
    {
        $classes = DB::table('balances')->select('target_type')->distinct()->get();
        foreach($classes as $c) {
            $class = $c->target_type;
            $objects = $class::all();
            foreach($objects as $obj) {
                $latest = $obj->current_balance;
                $new = $latest->replicate();
                $latest->date = $latest_date;
                $latest->current = false;
                $latest->save();
                $new->save();
            }
        }
    }

    public function getCurrentBalanceAttribute()
    {
        $balance = $this->balances()->where('current', true)->first();
        if ($balance == null) {
            $balance = $this->balances()->where('current', false)->first();
            if ($balance == null) {
                $balance = $this->fixFirstBalance();
            }
            else {
                $balance->current = true;
                $balance->save();
            }
        }

        return $balance;
    }

    public function getCurrentBalanceAmountAttribute()
    {
        $balance = $this->current_balance;
        return $balance->bank + $balance->cash;
    }

    public function alterBalance($amount, $type = 'bank')
    {
        if (is_string($type)) {
            $type = [$type];
        }

        $balance = $this->current_balance;

        foreach ($type as $t) {
            $balance->$t += $amount;
        }

        $balance->save();
    }

    abstract public static function balanceFields();
}
