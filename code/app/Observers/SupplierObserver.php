<?php

namespace App\Observers;

use Auth;

use App\Supplier;
use App\Role;

class SupplierObserver
{
    public function created(Supplier $supplier)
    {
        $user = Auth::user();

        if ($user) {
            $roles = Role::havingAction('supplier.modify');
            foreach($roles as $r) {
                $user->addRole($r, $supplier);
            }

            $roles = Role::havingAction('supplier.orders');
            foreach($roles as $r) {
                $user->addRole($r, $supplier);
            }

            $roles = Role::havingAction('supplier.shippings');
            foreach($roles as $r) {
                $user->addRole($r, $supplier);
            }
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
