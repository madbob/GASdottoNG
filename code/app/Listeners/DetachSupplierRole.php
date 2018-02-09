<?php

namespace App\Listeners;

use App\Events\SupplierDeleting;
use App\Role;

class DetachSupplierRole
{
    public function __construct()
    {
        //
    }

    public function handle(SupplierDeleting $event)
    {
        $roles = Role::rolesByClass('App\Supplier');
        foreach($roles as $role) {
            $users = $role->usersByTarget($event->supplier);
            foreach($users as $u)
                $u->removeRole($role, $event->supplier);
        }
    }
}
