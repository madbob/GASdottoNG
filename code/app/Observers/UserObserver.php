<?php

namespace App\Observers;

use App\Exceptions\IllegalArgumentException;

use App\User;
use App\Role;
use App\Delivery;

class UserObserver
{
    public function creating(User $user)
    {
        $test = User::withTrashed()->where('username', $user->username)->first();
        if ($test != null) {
            throw new IllegalArgumentException(_i('Username già assegnato'), 'username');
        }
    }

    private function createdFriend($user)
    {
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
        return $user;
    }

    public function created(User $user)
    {
        if ($user->isFriend()) {
            $user = $this->createdFriend($user);
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

    public function updating(User $user)
    {
        $test = User::withTrashed()->where('id', '!=', $user->id)->where('username', $user->username)->first();
        if ($test != null) {
            throw new IllegalArgumentException(_i('Username già assegnato'), 'username');
        }

        if (filled($user->card_number)) {
            $test = User::where('id', '!=', $user->id)->where('gas_id', $user->gas_id)->where('card_number', $user->card_number)->first();
            if ($test != null) {
                throw new IllegalArgumentException(_i('Numero tessera già assegnato'), 'card_number');
            }
        }
    }
}
