<?php

namespace App;

use App\Exceptions\AuthException;
use Auth;
use Hash;
use Illuminate\Http\Request;

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

    public function update(Request $request, $id)
    {
        $this->ensureAuthAdmin();

        DB::beginTransaction();

        $user = $this->show($id);

        $user->username = $request->input('username');
        $user->firstname = $request->input('firstname');
        $user->lastname = $request->input('lastname');
        $user->email = $request->input('email');
        $user->phone = $request->input('phone');
        $user->birthday = $this->decodeDate($request->input('birthday'));
        $user->member_since = $this->decodeDate($request->input('member_since'));
        $user->taxcode = $request->input('taxcode');
        $user->family_members = $request->input('family_members');
        $user->card_number = $request->input('card_number');

        $password = $request->input('password');
        if ($password != '') {
            $user->password = Hash::make($password);
        }

        $user->save();

        DB::commit();

        return $user;
    }

    public function store(Request $request)
    {
        $this->ensureAuthAdmin();

        $creator = Auth::user();

        $user = new User();
        $user->id = $request->input('username');
        $user->gas_id = $creator->gas->id;
        $user->member_since = date('Y-m-d', time());
        $user->username = $request->input('username');
        $user->firstname = $request->input('firstname');
        $user->lastname = $request->input('lastname');
        $user->email = $request->input('email');
        $user->password = Hash::make($request->input('password'));
        $user->current_balance = 0;
        $user->previous_balance = 0;

        DB::beginTransaction();

        $user->save();

        DB::commit();

        return $user;
    }
}
