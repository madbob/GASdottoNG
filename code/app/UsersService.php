<?php

namespace App;

use Auth;
use App\Exceptions\AuthException;

class UsersService
{

    private function ensureAuth()
    {
        if (Auth::check()) {
            return;
        }

        throw new AuthException(401);
    }

    private function ensureAuthAdminOrView()
    {
        $this->ensureAuth();

        $user = Auth::user();
        if ($user->gas->userCan('users.admin|users.view')) {
            return;
        }

        throw new AuthException(403);
    }

    private function ensureAuthAdmin()
    {
        $this->ensureAuth();

        $user = Auth::user();
        if ($user->gas->userCan('users.admin')) {
            return;
        }

        throw new AuthException(403);
    }

    public function listUsers()
    {
        $this->ensureAuthAdminOrView();

        $users = User::orderBy('lastname', 'asc')->get();

        return $users;
    }

    public function search($term)
    {
        $this->ensureAuthAdminOrView();

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

    public function show($id)
    {
        $this->ensureAuthAdminOrView();

        return User::findOrFail($id);
    }

    public function destroy($id)
    {
        $this->ensureAuthAdmin();

        DB::beginTransaction();

        $user = $this->show($id);

        $user->delete();

        DB::commit();
    }
}
