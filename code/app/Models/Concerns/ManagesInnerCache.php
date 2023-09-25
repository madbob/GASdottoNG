<?php

/*
    Queste funzioni permettono di avere una cache temporanea dei valori
    calcolati da ogni istanza dei modelli.
    Di default ogni istanza ha la sua propria cache locale, ma abilitando (nel
    costruttore di ciascun modello) la cache globale, tutte le istanze della
    stessa entità (ovvero: con lo stesso ID) condividono gli stessi valori.
    Da usare in modo oculato, in particolare per i modelli che potrebbero avere
    valori diversi a seconda del GAS abilitato (pur rappresentando la stessa
    entità, ovvero avendo lo stesso ID)
*/

namespace App\Models\Concerns;

trait ManagesInnerCache
{
    private $inner_runtime_cache;
    private $uses_global_cache = false;
    private $global_store = null;

    protected function enableGlobalCache()
    {
        $this->uses_global_cache = true;
        $this->global_store = app()->make('TempCache');
    }

    private function globalKey($name)
    {
        if ($this->uses_global_cache == false) {
            $hub = app()->make('GlobalScopeHub');
            if ($hub->hubRequired() && $hub->enabled()) {
                $gas_id = $hub->getGas();
                return sprintf('%s_%s_%s', $gas_id, $this->id, $name);
            }
        }

        return sprintf('%s_%s', $this->id, $name);
    }

    protected function innerCache($name, $function)
    {
        if ($this->uses_global_cache) {
            $name = $this->globalKey($name);

            if ($this->global_store->has($name) == false) {
                $value = $function($this);
                $this->global_store->put($name, $value);
                $ret = $value;
            }
            else {
                $ret = $this->global_store->get($name);
            }
        }
        else {
            if (!isset($this->inner_runtime_cache[$name])) {
                $this->inner_runtime_cache[$name] = $function($this);
            }

            $ret = $this->inner_runtime_cache[$name];
        }

        return $ret;
    }

    protected function setInnerCache($name, $value)
    {
        if ($this->uses_global_cache) {
            $name = $this->globalKey($name);
            $this->global_store->put($name, $value);
        }
        else {
            $this->inner_runtime_cache[$name] = $value;
        }
    }

    protected function emptyInnerCache($name)
    {
        if ($this->uses_global_cache) {
            $name = $this->globalKey($name);
            $this->global_store->forget($name);
        }
        else {
            unset($this->inner_runtime_cache[$name]);
        }
    }
}
