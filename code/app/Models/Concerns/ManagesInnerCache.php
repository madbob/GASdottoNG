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
    private $innerRuntimeCache;

    private $usesGlobalCache = false;

    private $globalStore = null;

    protected function enableGlobalCache()
    {
        $this->usesGlobalCache = true;
        $this->globalStore = app()->make('TempCache');
    }

    private function globalKey($name)
    {
        if ($this->usesGlobalCache === false) {
            $hub = app()->make('GlobalScopeHub');
            if ($hub->hubRequired() && $hub->enabled()) {
                $gas_id = $hub->getGas();

                return sprintf('%s_%s_%s', $gas_id, $this->id, $name);
            }
        }

        return sprintf('%s_%s', $this->id, $name);
    }

    protected function hasInnerCache($name)
    {
        if ($this->usesGlobalCache) {
            $name = $this->globalKey($name);

            return $this->globalStore->has($name);
        }
        else {
            return isset($this->innerRuntimeCache[$name]);
        }
    }

    protected function innerCache($name, $function)
    {
        if ($this->usesGlobalCache) {
            $name = $this->globalKey($name);

            if ($this->globalStore->has($name) === false) {
                $value = $function($this);
                $this->globalStore->put($name, $value);
                $ret = $value;
            }
            else {
                $ret = $this->globalStore->get($name);
            }
        }
        else {
            if (! isset($this->innerRuntimeCache[$name])) {
                $this->innerRuntimeCache[$name] = $function($this);
            }

            $ret = $this->innerRuntimeCache[$name];
        }

        return $ret;
    }

    protected function setInnerCache($name, $value)
    {
        if ($this->usesGlobalCache) {
            $name = $this->globalKey($name);
            $this->globalStore->put($name, $value);
        }
        else {
            $this->innerRuntimeCache[$name] = $value;
        }
    }

    protected function emptyInnerCache($name)
    {
        if ($this->usesGlobalCache) {
            $name = $this->globalKey($name);
            $this->globalStore->forget($name);
        }
        else {
            unset($this->innerRuntimeCache[$name]);
        }
    }
}
