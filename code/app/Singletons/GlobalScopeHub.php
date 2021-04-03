<?php

namespace App\Singletons;

use App\Gas;

class GlobalScopeHub
{
    private $enabled_global_scopes = true;
    private $gas_id = null;

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
    }

    public function getGas()
    {
        return $this->gas_id;
    }

    public function getGasObj()
    {
        return Gas::find($this->gas_id);
    }
}
