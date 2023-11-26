<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Config;

trait Configurable
{
    public function configs(): HasMany
    {
        return $this->hasMany(Config::class);
    }

    private function availableConfigs()
    {
        return systemParameters('Config');
    }

    public function getConfig($name)
    {
        foreach ($this->configs as $conf) {
            if ($conf->name == $name) {
                return $conf->value;
            }
        }

        $defined = $this->availableConfigs();
        if (!isset($defined[$name])) {
            \Log::error(_i('Configurazione GAS non prevista'));
            return '';
        }
        else {
            $this->setConfig($name, $defined[$name]->default());
            $this->load('configs');
            return $this->getConfig($name);
        }
    }

    public function setConfig($name, $value)
    {
        if (is_object($value) || is_array($value)) {
            $value = json_encode($value);
        }

        foreach ($this->configs as $conf) {
            if ($conf->name == $name) {
                $conf->value = $value;
                $conf->save();
                return;
            }
        }

        $conf = new Config();
        $conf->name = $name;
        $conf->value = $value;
        $conf->gas_id = $this->id;
        $conf->save();
    }

    public function setManyConfigs($request, $params)
    {
        $configs = $this->availableConfigs();

        foreach($params as $param) {
            $c = $configs[$param];
            $c->handleSave($this, $request);
        }
    }

    /*
        Questa funzione permette di accedere direttamente alle configurazioni
        del GAS, usandone il nome (definito da ciascun parametro definito nelle
        classi nel namespace App\Parameters\Config)
    */
    public function getAttribute($key)
    {
        $configs = $this->availableConfigs();
        $c = $configs[$key] ?? null;

        if ($c) {
            return $c->asAttribute($this);
        }
        else {
            return parent::getAttribute($key);
        }
    }
}
