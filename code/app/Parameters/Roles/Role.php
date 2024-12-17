<?php

/*
    Questa classe rappresenta un ruolo di default.
    Tutte le altre classi in App\Parameters\Roles vengono usate dal
    seeder di base per inizializzare una nuova installazione e permettono di
    "estendere" il comportamento di App\Role: un App\Parameters\Roles è
    correlato ad un App\Role che ha il medesimo identifier, i ruoli
    personalizzati creati sulla singola istanza hanno sempre $identifier = ''
*/

namespace App\Parameters\Roles;

use App\Parameters\Parameter;
use App\Role as RoleModel;

abstract class Role extends Parameter
{
    public function create()
    {
        $type = new RoleModel();
        $type->identifier = $this->identifier();
        $type = $this->initNew($type);
        $type->save();
    }

    public function enabled()
    {
        return true;
    }

    protected function getID($identifier)
    {
        $target = roleByIdentifier($identifier);
        if (is_null($target)) {
            throw new \Exception('Role not found', 1);
        }

        return $target->id;
    }

    abstract public function initNew($type);
}
