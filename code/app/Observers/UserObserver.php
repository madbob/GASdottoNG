<?php

namespace App\Observers;

use App\User;
use App\Role;
use App\Delivery;

class UserObserver
{
    public function created(User $user)
    {
        if ($user->isFriend()) {
            $default_role = $user->gas->roles['friend'] ?? -1;
            $role = Role::find($default_role);
            if (is_null($role)) {
                $default_role = $user->gas->roles['user'];
                $role = Role::find($default_role);
            }

            if ($role) {
                $user->addRole($role, $user->gas);
            }

            $user->preferred_delivery_id = '';
        }
        else {
            $default_role = $user->gas->roles['user'];
            $role = Role::find($default_role);
            if ($role) {
                $user->addRole($role, $user->gas);
            }

            $fallback_delivery = Delivery::where('default', true)->first();
            if ($fallback_delivery != null) {
                $user->preferred_delivery_id = $fallback_delivery->id;
            }
        }

        $user->save();
    }
}
