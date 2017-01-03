<?php

namespace App;

use App\Exceptions\AuthException;
use Auth;
use DB;
use Hash;

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

        $gasID = Auth::user()->gas['id'];

        $users = User::where('gas_id', '=', $gasID)->orderBy('lastname', 'asc')->get();

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
                'value' => $fullname,
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

    public function update($id, array $request)
    {
        $this->ensureAuthAdmin();

        DB::beginTransaction();

        $user = $this->show($id);

        $user->username = $request['username'];
        $user->firstname = $request['firstname'];
        $user->lastname = $request['lastname'];
        $user->email = $request['email'];
        $user->phone = $request['phone'];
        $user->birthday = decodeDate($request['birthday']);
        $user->member_since = decodeDate($request['member_since']);
        $user->taxcode = $request['taxcode'];
        $user->family_members = $request['family_members'];
        $user->card_number = $request['card_number'];

        $password = $request['password'];
        if ($password != '') {
            $user->password = Hash::make($password);
        }

        $user->save();

        DB::commit();

        return $user;
    }

    public function store(array $request)
    {
        $this->ensureAuthAdmin();

        $creator = Auth::user();

        $user = new User();
        $user->id = $request['username'];
        $user->gas_id = $creator->gas->id;
        $user->member_since = date('Y-m-d', time());
        $user->username = $request['username'];
        $user->firstname = $request['firstname'];
        $user->lastname = $request['lastname'];
        $user->email = $request['email'];
        $user->password = Hash::make($request['password']);
        $user->balance = 0;

        DB::beginTransaction();

        $user->save();

        DB::commit();

        return $user;
    }
}
