<?php

namespace App\Parameters\Config;

use App\Parameters\Parameter;

abstract class Config extends Parameter
{
    public function asAttribute($gas)
    {
        $value = $gas->getConfig($this->identifier());

        switch ($this->type()) {
            case 'object':
            case 'array':
                /*
                    Qui faccio un merge tra le configurazioni esistenti e quelle
                    di default, onde avere dei valori validi anche per i nuovi
                    campi introdotti in corso d'opera
                */
                $default = (array) $this->default();
                $value = (array) json_decode($value);
                return array_merge($default, $value);

            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);

            default:
                return (string) $value;
        }
    }

    /*
        Questa funzione gestisce il salvataggio della configurazione, così come
        rappresentata nei relativi pannelli.
        Le configurazioni di tipo "object", che richiedono vari e variegati
        sotto-attributi, dovrebbero sempre sovrascrivere questa funzione per
        organizzarli nel modo opportuno
    */
    public function handleSave($gas, $request)
    {
        $id = $this->identifier();

        switch ($this->type()) {
            case 'boolean':
                $value = $request->has($id) ? '1' : '0';
                break;

            case 'float':
            case 'number':
                $value = $request->input($id, 0);
                break;

            case 'array':
                $value = $request->input($id, []);
                break;

            case 'object':
                throw new \Exception("Le configurazioni di tipo 'object' devono avere una propria funzione di salvataggio", 1);

            default:
                $value = $request->input($id);
                break;
        }

        $gas->setConfig($id, $value);
    }

    abstract public function identifier();

    abstract public function type();

    abstract public function default();
}
