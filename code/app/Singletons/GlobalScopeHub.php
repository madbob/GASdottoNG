<?php

namespace App\Singletons;

use App\Gas;

class GlobalScopeHub
{
    private $has_many_gas = false;

    private $enabled_global_scopes = true;

    private $gas_id = null;

    private $gas = null;

    public function __construct()
    {
        $this->has_many_gas = Gas::count() > 1;
    }

    public function hubRequired()
    {
        return $this->has_many_gas;
    }

    public function enable(bool $active)
    {
        $this->enabled_global_scopes = $active;
    }

    public function enabled(): bool
    {
        return $this->enabled_global_scopes;
    }

    public function setGas($gas_id)
    {
        if (is_object($gas_id)) {
            $this->gas_id = $gas_id->id;
            $this->gas = $gas_id;
        }
        else {
            $this->gas_id = $gas_id;
            $this->gas = Gas::find($this->gas_id);
        }
    }

    public function getGas()
    {
        return $this->gas_id;
    }

    public function getGasObj()
    {
        return $this->gas;
    }

    /*
        Questa funzione permette di eseguire una determinata funzione con o
        senza il global scope per il GAS attuale, a seconda della condizione
        passata come parametro
    */
    public function executedForAll($condition, $function)
    {
        if ($condition) {
            $this->enable(false);
        }

        $ret = $function();

        if ($condition) {
            $this->enable(true);
        }

        return $ret;
    }
}
