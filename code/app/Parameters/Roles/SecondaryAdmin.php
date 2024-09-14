<?php

namespace App\Parameters\Roles;

class SecondaryAdmin extends Role
{
    public function identifier()
    {
        return 'secondary_admin';
    }

	public function initNew($type)
    {
        $type->name = _i('Amministratore GAS Secondario');
        $type->system = true;
		$type->actions = 'gas.access,gas.config,supplier.view,supplier.book,supplier.add,users.admin,users.movements,movements.admin,notifications.admin';
		$type->parent_id = $this->getID('admin');
        return $type;
    }

	public function enabled()
	{
		return currentAbsoluteGas()->multigas;
	}
}
