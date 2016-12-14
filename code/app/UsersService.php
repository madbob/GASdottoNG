<?php

namespace App;

use Auth;
use App\Exceptions\AuthException;

class UsersService
{

    private function ensureAuth()
    {
        if (!Auth::check()) {
            throw new AuthException(401);
        }

        $user = Auth::user();
        if ($user->gas->userCan('users.admin|users.view') == false) {
            throw new AuthException(403);
        }
    }

    public function listUsers()
    {
        $this->ensureAuth();

        $users = User::orderBy('lastname', 'asc')->get();

        return $users;
    }

    public function search($term)
    {
        $this->ensureAuth();

        $users = User::where('firstname', 'LIKE', "%$term%")->orWhere('lastname', 'LIKE', "%$term%")->get();
        $ret = array();

        foreach ($users as $user) {
            $fullname = $user->printableName();

            $u = (object)array(
                'id' => $user->id,
                'label' => $fullname,
                'value' => $fullname
            );

            $ret[] = $u;
        }

        return $ret;
    }

}
