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
            return $this->fixFirstBalance();
        }
        else {
            $latest = $this->balances()->where('current', false)->first();
            $new = $latest->replicate();

            $new->date = date('Y-m-d G:i:s');
            $new->current = true;
            $new->save();
            return $new;
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
        $current_status = [];

        $classes = DB::table('balances')->select('target_type')->distinct()->get();
        foreach($classes as $c) {
            $class = $c->target_type;

            $current_status[$class] = [];
            $fields = $class::balanceFields();

            if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($class)))
                $objects = $class::withTrashed()->get();
            else
                $objects = $class::all();

            foreach($objects as $obj) {
                $now = [];
                $cb = $obj->current_balance;

                if (is_null($cb)) {
                    foreach($fields as $field => $name)
                        $now[$field] = 0;
                }
                else {
                    foreach($fields as $field => $name)
                        $now[$field] = $cb->$field;
                }

                $current_status[$class][$obj->id] = $now;

                $obj->resetCurrentBalance();
            }
        }

        return $current_status;
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

    /*
        Si aspetta come parametro un array formattato come quello restituito da
        resetAllCurrentBalances()

        [
            'Classe' => [
                'ID Oggetto' => [
                    'cash' => XXX,
                    'bank' => XXX,
                ],
                'ID Oggetto' => [
                    'cash' => XXX,
                    'bank' => XXX,
                ],
            ]
        ]
    */
    public static function compareBalances($old_balances)
    {
        $diff = [];

        foreach($old_balances as $class => $ids) {
            $fields = $class::balanceFields();

            foreach($ids as $id => $old) {
                $obj = $class::find($id);
                if (is_null($obj))
                    continue;

                $cb = $obj->current_balance;
                foreach($fields as $field => $name) {
                    if ($old[$field] != $cb->$field) {
                        $diff[$obj->printableName()] = [
                            $old[$field],
                            $cb->$field
                        ];

                        break;
                    }
                }
            }
        }

        return $diff;
    }

    public function getCurrentBalanceAttribute()
    {
        $proxy = $this->getBalanceProxy();

        if(is_null($proxy)) {
            $balance = $this->balances()->where('current', true)->first();
            if (is_null($balance)) {
                $balance = $this->balances()->where('current', false)->first();
                if (is_null($balance)) {
                    $balance = $this->fixFirstBalance();
                }
                else {
                    $balance->current = true;
                    $balance->save();
                }
            }

            return $balance;
        }
        else {
            return $proxy->current_balance;
        }
    }

    public function getCurrentBalanceAmountAttribute()
    {
        $balance = $this->current_balance;
        return $balance->bank + $balance->cash;
    }

    public function alterBalance($amount, $type = 'bank')
    {
        $proxy = $this->getBalanceProxy();

        if(is_null($proxy)) {
            if (is_string($type)) {
                $type = [$type];
            }

            $balance = $this->current_balance;

            foreach ($type as $t) {
                $balance->$t += $amount;
            }

            $balance->save();
        }
        else {
            $proxy->alterBalance($amount, $type);
        }
    }

    /*
        Questa funzione è destinata ad essere sovrascritta ove opportuno
        (laddove esistono classi che possono essere oggetti di un movimento, ma
        di fatto rappresentano il saldo di qualcos altro. Cfr. gli ordini nei
        confronti dei fornitori)
    */
    public function getBalanceProxy()
    {
        return null;
    }

    abstract public static function balanceFields();
}
