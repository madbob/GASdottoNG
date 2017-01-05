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

    public function listUsers($term = '')
    {
        $this->ensureAuthAdminOrView();

        $gasID = Auth::user()->gas['id'];

        $query = User::where('gas_id', '=', $gasID);

        if (!empty($term)) {
            $query->where(function ($query) use ($term) {
                $query->where('firstname', 'LIKE', "%$term%")->orWhere('lastname', 'LIKE', "%$term%");
            });
        }

        $users = $query->orderBy('lastname', 'asc')->get();

        return $users;
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

        return $user;
    }

    public function update($id, array $request)
    {
        $this->ensureAuthAdmin();

        DB::beginTransaction();

        $user = $this->show($id);

        $this->setIfSet($user, $request, 'username');
        $this->setIfSet($user, $request, 'firstname');
        $this->setIfSet($user, $request, 'lastname');
        $this->setIfSet($user, $request, 'email');
        $this->setIfSet($user, $request, 'phone');
        $this->transformAndSetIfSet($user, $request, 'birthday', "decodeDate");
        $this->transformAndSetIfSet($user, $request, 'member_since', "decodeDate");
        $this->setIfSet($user, $request, 'taxcode');
        $this->setIfSet($user, $request, 'family_members');
        $this->setIfSet($user, $request, 'card_number');

        $this->transformAndSetIfSet($user, $request, 'password', function ($password) {
            if ($password == '') {
                return $password;
            }
            return Hash::make($password);
        });

        $user->save();

        DB::commit();

        return $user;
    }

    private function setIfSet($target, array $source, $key)
    {
        if (isset($source[$key])) {
            $target->$key = $source[$key];
        }
    }

    private function transformAndSetIfSet($target, array $source, $key, $transformerFunction)
    {
        if (isset($source[$key])) {
            $target->$key = $transformerFunction($source[$key]);
        }
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
