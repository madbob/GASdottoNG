<?php

namespace App\Observers;

use Auth;

use App\Supplier;
use App\Role;

class SupplierObserver
{
    private function attachAllRoles($user, $supplier)
    {
        $actions = ['supplier.modify', 'supplier.orders', 'supplier.shippings'];

        foreach($actions as $action) {
            $roles = Role::havingAction($action);
            foreach($roles as $r) {
                $user->addRole($r, $supplier);
            }
        }
    }

    public function created(Supplier $supplier)
    {
        $user = Auth::user();
        if ($user) {
            $this->attachAllRoles($user, $supplier);
        }
    }

    public function deleted(Supplier $supplier)
    {
        $roles = rolesByClass('App\Supplier');
        foreach($roles as $role) {
            $users = $role->usersByTarget($supplier);
            foreach($users as $u) {
                $u->removeRole($role, $supplier);
            }
        }
    }
}
