<?php

namespace App\Parameters\Roles;

class Friend extends Role
{
    public function identifier()
    {
        return 'friend';
    }

    public function initNew($type)
    {
        $type->name = __('user.friend');
        $type->system = true;
        $type->actions = 'users.self,supplier.view,supplier.book';
        $type->parent_id = $this->getID('user');

        return $type;
    }

    public function enabled()
    {
        return someoneCan('users.subusers');
    }
}
