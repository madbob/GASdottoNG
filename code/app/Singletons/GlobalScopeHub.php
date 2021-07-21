<?php

namespace App\Singletons;

use App\Gas;

class GlobalScopeHub
{
    private $enabled_global_scopes = true;
    private $gas_id = null;
    private $gas = null;

    public function enable($active)
    {
        $this->enabled_global_scopes = $active;
    }

    public function enabled()
    {
        return $this->enabled_global_scopes;
    }

    public function setGas($gas_id)
    {
        $this->gas_id = $gas_id;
        $this->gas = Gas::find($this->gas_id);
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
