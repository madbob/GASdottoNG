<?php

namespace App\Parameters\Roles;

class Admin extends Role
{
    public function identifier()
    {
        return 'admin';
    }

	public function initNew($type)
    {
        $type->name = _i('Amministratore');
        $type->system = true;
		$type->actions = 'gas.access,gas.permissions,gas.config,supplier.view,supplier.add,users.admin,users.movements,movements.admin,movements.types,categories.admin,measures.admin,gas.statistics,notifications.admin';
        return $type;
    }
}
