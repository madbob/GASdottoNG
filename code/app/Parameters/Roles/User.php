<?php

namespace App\Parameters\Roles;

class User extends Role
{
    public function identifier()
    {
        return 'user';
    }

    public function initNew($type)
    {
        $type->name = __('user.name');
        $type->system = true;
        $type->actions = 'users.self,users.view,supplier.view,supplier.book';
        $type->parent_id = $this->getID('admin');

        return $type;
    }
}
