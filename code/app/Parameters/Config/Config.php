<?php

namespace App\Parameters\Config;

use App\Parameters\Parameter;

abstract class Config extends Parameter
{
    public function asAttribute($gas)
    {
        $value = $gas->getConfig($this->identifier());

        switch($this->type()) {
            case 'object':
            case 'array':
                return (array) json_decode($value);

            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);

            default:
                return (string) $value;
        }
    }

    public abstract function type();
    public abstract function default();
}
