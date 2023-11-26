<?php

namespace App\Parameters\Config;

use App\Role;

class Roles extends Config
{
    public function identifier()
    {
        return 'roles';
    }

    public function type()
    {
        return 'object';
    }

    public function default()
    {
        $default_role = Role::where('name', 'Utente')->first();
        $friend_role = Role::where('name', 'Amico')->first();
        $secondary_admin_role = Role::where('name', 'Amministratore GAS Secondario')->first();

        return (object) [
            'user' => $default_role ? $default_role->id : -1,
            'friend' => $friend_role ? $friend_role->id : -1,
            'multigas' => $secondary_admin_role ? $secondary_admin_role->id : -1
        ];
    }

    public function handleSave($gas, $request)
    {
        $role_service = app()->make('RolesService');

        foreach(['user', 'friend', 'multigas'] as $role_type) {
            $input_key = sprintf('roles->%s', $role_type);
            if ($request->has($input_key)) {
                $role = $request->input($input_key);
                $role_service->setMasterRole($gas, $role_type, $role);
    		}
        }
    }
}
