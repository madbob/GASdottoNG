<?php

/*
    Questo opera essenzialmente come Cache::store('array') ma senza l'overhead
    della gestione delle scadenze (questa è una cache volatile, vale solo per
    l'esecuzione corrente).
    Reminder: non utilizzare "array" come driver per le cache native Laravel, 
    qua e là vengono davvero usate per conservare valori temporanei tra una
    sessione e l'altra
*/

namespace App\Singletons;

class TempCache
{
    private $store = [];

    public function put($key, $value)
    {
        $this->store[$key] = $value;
    }

    public function get($key)
    {
        return $this->store[$key] ?? null;
    }

    public function has($key)
    {
        return isset($this->store[$key]);
    }

    public function forget($key)
    {
        unset($this->store[$key]);
    }
}
