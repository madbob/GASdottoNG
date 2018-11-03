<?php

namespace App;

use DB;
use Log;

trait CreditableTrait
{
    public function balances()
    {
        $proxy = $this->getBalanceProxy();
        if (is_null($proxy))
            return $this->morphMany('App\Balance', 'target')->orderBy('date', 'desc');
        else
            return $proxy->balances();
    }

    private function fixFirstBalance()
    {
        $proxy = $this->getBalanceProxy();
        if (is_null($proxy))
            $proxy = $this;

        $balance = new Balance();
        $balance->target_id = $proxy->id;
        $balance->target_type = get_class($proxy);
        $balance->bank = 0;
        $balance->cash = 0;
        $balance->gas = 0;
        $balance->suppliers = 0;
        $balance->deposits = 0;
        $balance->paypal = 0;
        $balance->satispay = 0;
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

            if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($class)))
                $objects = $class::withTrashed()->get();
            else
                $objects = $class::all();

            foreach($objects as $obj) {
                $proxy = $obj->getBalanceProxy();
                if ($proxy != null)
                    $obj = $proxy;

                $class = get_class($obj);
                $fields = $class::balanceFields();

                if (!isset($current_status[$class]))
                    $current_status[$class] = [];

                /*
                    Attenzione: qui prendo in considerazione gli eventuali
                    "proxy" degli elementi coinvolti nei movimenti, che
                    all'interno di questo ciclo possono anche presentarsi più
                    volte (e.g. diversi ordini per lo stesso fornitore).
                    Ma il reset lo devo fare una volta sola, altrimenti cancello
                    a ritroso i saldi salvati passati.
                */
                if (!isset($current_status[$class][$obj->id])) {
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
        }

        return $current_status;
    }

    public static function duplicateAllCurrentBalances($latest_date)
    {
        $current_status = [];

        $classes = DB::table('balances')->select('target_type')->distinct()->get();
        foreach($classes as $c) {
            $class = $c->target_type;

            if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($class)))
                $objects = $class::withTrashed()->get();
            else
                $objects = $class::all();

            foreach($objects as $obj) {
                $proxy = $obj->getBalanceProxy();
                if ($proxy != null) {
                    $obj = $proxy;
                    $class = get_class($obj);
                }

                if (!isset($current_status[$class]))
                    $current_status[$class] = [];

                if (!isset($current_status[$class][$obj->id])) {
                    $latest = $obj->current_balance;
                    $new = $latest->replicate();

                    $latest->date = $latest_date;
                    $latest->current = false;
                    $latest->save();
                    $new->current = true;
                    $new->save();

                    $current_status[$class][$obj->id] = true;
                }
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
            foreach($ids as $id => $old) {
                $obj = $class::tFind($id);
                if (is_null($obj))
                    continue;

                $proxy = $obj->getBalanceProxy();
                if ($proxy != null) {
                    $obj = $proxy;
                    $proxy_class = get_class($obj);
                    $fields = $proxy_class::balanceFields();
                }
                else {
                    $fields = $class::balanceFields();
                }

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
                if (!isset($balance->$t))
                    $balance->$t = 0;
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
