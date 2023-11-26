<?php

/*
    Questa regola gestisce lo scope di alcuni modelli, permettendo di filtrare
    solo quelli di pertinenza del proprio GAS (quello dell'utente attualmente
    autenticato).
    In circostanze particolari, questo scope può essere alternato per filtrare i
    contenuti di un altro GAS o per disabilitare del tutto lo scope e dunque
    accedere ai dati di tutti i GAS.
*/

namespace App\Scopes;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

use App;

class RestrictedGAS implements Scope
{
    private $key = null;
    private $involve_trashed = false;

    public function __construct($key = 'gas', $involve_trashed = false)
    {
        $this->key = $key;
        $this->involve_trashed = $involve_trashed;
    }

    private function initInnerQuery($gas_id)
    {
        if ($this->involve_trashed) {
            return function($query) use ($gas_id) {
                $query->withTrashed()->where('gas_id', $gas_id);
            };
        }
        else {
            return function($query) use ($gas_id) {
                $query->where('gas_id', $gas_id);
            };
        }
    }

    public function apply(Builder $builder, Model $model)
    {
        $hub = App::make('GlobalScopeHub');

        /*
            Lo scope relativo all'assegnazione del GAS lo attivo solo se
            effettivamente c'è più di un GAS sull'istanza, altrimenti è
            essenzialmente inutile (e genera JOIN a sproposito)
        */
        if ($hub->hubRequired() && $hub->enabled()) {
            $gas_id = $hub->getGas();
            $inner_query = $this->initInnerQuery($gas_id);
            $models = explode('.', $this->key);

            if (count($models) == 1) {
                $builder->whereHas($models[0], $inner_query);
            }
            else {
                $builder->whereHas($models[0], function($query) use ($models, $inner_query) {
                    $query->whereHas($models[1], $inner_query);
                });
            }
        }
    }
}
