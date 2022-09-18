<?php

namespace App;

use Illuminate\Support\Arr;

use DB;
use Log;

trait CreditableTrait
{
    public function balances()
    {
        $proxy = $this->getBalanceProxy();
        if (is_null($proxy)) {
            return $this->morphMany('App\Balance', 'target')->orderBy('current', 'desc')->orderBy('date', 'desc');
        }
        else {
            return $proxy->balances();
        }
    }

    private function fixFirstBalance($currency)
    {
        $proxy = $this->getActualObject();
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
        $balance->currency_id = $currency->id;
        $balance->date = date('Y-m-d');
        $balance->save();
        return $balance;
    }

    private function resetCurrentBalance($currency)
    {
        $this->currentBalance($currency)->delete();

        if ($this->balances()->where('currency_id', $currency->id)->count() == 0) {
            return $this->fixFirstBalance($currency);
        }
        else {
            $latest = $this->balances()->where('current', false)->where('currency_id', $currency->id)->first();
            if (is_null($latest)) {
                return $this->fixFirstBalance($currency);
            }
            else {
                $new = $latest->replicate();
                $new->date = date('Y-m-d G:i:s');
                $new->current = true;
                $new->save();
                return $new;
            }
        }
    }

    public function resetCurrentBalances(&$current_status)
    {
        $currencies = Currency::enabled();

        $obj = $this->getActualObject();
        $class = get_class($obj);
        $fields = $obj->balanceFields();
        $now = [];

        /*
            Attenzione: qui prendo in considerazione gli eventuali
            "proxy" degli elementi coinvolti nei movimenti, che
            all'interno di questo ciclo possono anche presentarsi più
            volte (e.g. diversi ordini per lo stesso fornitore).
            Ma il reset lo devo fare una volta sola, altrimenti cancello
            a ritroso i saldi salvati passati.
        */
        foreach ($currencies as $curr) {
            if (!isset($current_status[$curr->id][$class][$obj->id])) {
                $cb = $obj->currentBalance($curr);
                foreach ($fields as $field => $name) {
                    $now[$field] = $cb->$field;
                }

                $current_status[$curr->id][$class][$obj->id] = $now;
                $obj->resetCurrentBalance($curr);
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

        foreach($old_balances as $currency_id => $data) {
            $currency = Currency::find($currency_id);

            foreach($data as $class => $ids) {
                foreach($ids as $id => $old) {
                    $obj = $class::tFind($id);
                    if (is_null($obj)) {
                        continue;
                    }

                    $cb = $obj->currentBalance($currency);

                    foreach ($old as $field => $old_value) {
                        if ($old_value != $cb->$field) {
                            $key = sprintf('%s (%s)', $obj->printableName(), $currency->symbol);
                            $diff[$key] = [$old_value, $cb->$field];
                            break;
                        }
                    }
                }
            }
        }

        return $diff;
    }

    public function currentBalance($currency)
    {
        $proxy = $this->getActualObject();

        $balance = $proxy->balances()->where('current', true)->where('currency_id', $currency->id)->first();
        if (is_null($balance)) {
            $balance = $this->balances()->where('current', false)->where('currency_id', $currency->id)->first();
            if (is_null($balance)) {
                $balance = $this->fixFirstBalance($currency);
            }
            else {
                $balance->current = true;
                $balance->save();
            }
        }

        return $balance;
    }

    public function extendedCurrentBalance($currency)
    {
        $balance = $this->currentBalance($currency);

        foreach($this->virtualBalances($currency) as $name => $value) {
            $balance->$name = $value->value;
        }

        return $balance;
    }

    public function currentBalanceAmount($currency = null)
    {
        if (is_null($currency)) {
            $currency = defaultCurrency();
        }

        $balance = $this->currentBalance($currency);
        return $balance->bank + $balance->cash;
    }

    public function alterBalance($amount, $currency, $type = 'bank')
    {
        $type = Arr::wrap($type);
        $balance = $this->currentBalance($currency);

        foreach ($type as $t) {
            if (!isset($balance->$t)) {
                $balance->$t = 0;
            }

            $balance->$t += $amount;
        }

        $balance->save();
    }

    public function getActualObject()
    {
        $proxy = $this->getBalanceProxy();
        if ($proxy != null) {
            return $proxy;
        }
        else {
            return $this;
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

    /*
        Questa funzione è destinata ad essere sovrascritta per includere nel
        saldo "esteso" (cfr. getExtendedCurrentBalanceAttribute()) valori
        dinamicamente calcolati
    */
    protected function virtualBalances($currency)
    {
        return [];
    }

    public function extendedBalanceFields()
    {
        $ret = $this->balanceFields();

        foreach($this->virtualBalances(null) as $name => $virtual) {
            $ret[$name] = $virtual->label;
        }

        return $ret;
    }

    abstract public static function commonClassName();
    abstract public function balanceFields();
}
