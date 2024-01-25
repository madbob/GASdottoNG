<?php

namespace App\Parameters\Roles;

class Referent extends Role
{
    public function identifier()
    {
        return 'referent';
    }

	public function initNew($type)
    {
        $type->name = _i('Referente');
        $type->system = true;
		$type->actions = 'supplier.modify,supplier.orders,supplier.shippings,supplier.movements,supplier.invoices';
		$type->parent_id = $this->getID('admin');
        return $type;
    }
}
