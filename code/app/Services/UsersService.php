<?php

namespace App\Services;

use App\Exceptions\AuthException;
use App\Exceptions\IllegalArgumentException;

use Auth;
use Log;
use DB;
use Hash;

use App\User;
use App\Role;

class UsersService extends BaseService
{
    public function list($term = '', $all = false)
    {
        $user = $this->ensureAuth(['users.admin' => 'gas', 'users.view' => 'gas']);
        $gas_id = $user->gas['id'];
        $query = User::with('roles')->where('parent_id', null)->where('gas_id', '=', $gas_id);

        if (!empty($term)) {
            $query->where(function ($query) use ($term) {
                $query->where('firstname', 'LIKE', "%$term%")->orWhere('lastname', 'LIKE', "%$term%");
            });
        }

        if ($all)
            $query->filterEnabled();

        $users = $query->orderBy('lastname', 'asc')->get();
        return $users;
    }

    public function show($id)
    {
        $user = Auth::user();
        if ($user == null) {
            throw new AuthException(401);
        }

        $searched = User::withTrashed()->findOrFail($id);

        if ($searched->testUserAccess() == false)
            $this->ensureAuth(['users.admin' => 'gas', 'users.view' => 'gas']);

        return $searched;
    }

    public function store(array $request)
    {
        /*
            Gli utenti col permesso di agire sul multi-gas devono poter creare i
            nuovi utenti amministratori
        */
        $creator = $this->ensureAuth(['users.admin' => 'gas', 'gas.multi' => 'gas']);

        $username = $request['username'];
        $test = User::where('username', $username)->first();
        if ($test != null) {
            throw new IllegalArgumentException(_i('Username già assegnato'), 'username');
        }

        $user = new User();
        $user->gas_id = $creator->gas->id;
        $user->member_since = date('Y-m-d', time());
        $user->username = $username;
        $user->firstname = $request['firstname'];
        $user->lastname = $request['lastname'];
        $user->password = Hash::make($request['password']);

        DB::transaction(function () use ($user) {
            $user->save();
        });

        return $user;
    }

    public function storeFriend(array $request)
    {
        $creator = $this->ensureAuth(['users.subusers' => 'gas']);

        $username = $request['username'];
        $test = User::withTrashed()->withoutGlobalScopes()->where('username', $username)->first();
        if ($test != null) {
            throw new IllegalArgumentException(_i('Username già assegnato'), 'username');
        }

        $user = new User();
        $user->parent_id = $creator->id;
        $user->gas_id = $creator->gas->id;
        $user->member_since = date('Y-m-d', time());
        $user->username = $username;
        $user->firstname = $request['firstname'];
        $user->lastname = $request['lastname'];
        $user->password = Hash::make($request['password']);

        DB::transaction(function () use ($user, $creator) {
            $user->save();

            $user_role = $creator->gas->roles['user'];
            $friend_role = $creator->gas->roles['friend'];

            if ($user_role != $friend_role) {
                $role = Role::find($friend_role);
                if ($role) {
                    $user->roles()->detach();
                    $user->addRole($role, $creator->gas);
                }
            }
        });

        return $user;
    }

    public function update($id, array $request)
    {
        $user = Auth::user();
        if ($user == null) {
            throw new AuthException(401);
        }

        if ($user->can('users.admin', $user->gas)) {
            $type = 1;
        }
        else if ($user->id == $id && $user->can('users.self', $user->gas)) {
            $type = 2;
        }
        else {
            throw new AuthException(403);
        }

        $user = DB::transaction(function () use ($id, $request, $type) {
            $user = $this->show($id);

            if (isset($request['username'])) {
                $username = $request['username'];
                $test = User::where('id', '!=', $user->id)->where('username', $username)->first();
                if ($test != null) {
                    throw new IllegalArgumentException(_i('Username già assegnato'), 'username');
                }
            }

            if (isset($request['card_number'])) {
                $card_number = $request['card_number'];
                $test = User::where('id', '!=', $user->id)->where('gas_id', $user->gas_id)->where('card_number', $card_number)->first();
                if ($test != null) {
                    throw new IllegalArgumentException(_i('Numero tessera già assegnato'), 'card_number');
                }
            }

            $this->setIfSet($user, $request, 'username');
            $this->setIfSet($user, $request, 'firstname');
            $this->setIfSet($user, $request, 'lastname');
            $this->transformAndSetIfSet($user, $request, 'birthday', "decodeDate");
            $this->setIfSet($user, $request, 'taxcode');
            $this->transformAndSetIfSet($user, $request, 'family_members', 'enforceNumber');
            $this->setIfSet($user, $request, 'preferred_delivery_id');

            if ($type == 1) {
                $this->transformAndSetIfSet($user, $request, 'member_since', "decodeDate");
                $this->setIfSet($user, $request, 'card_number');
            }

            if(isset($request['password']) && !empty($request['password'])) {
                $this->transformAndSetIfSet($user, $request, 'password', function ($password) {
                    return Hash::make($password);
                });
            }

            if(!empty($user->gas->rid['iban'])) {
                $rid_info['iban'] = $request['rid->iban'] ?? $user->rid['iban'];
                $rid_info['id'] = $request['rid->id'] ?? $user->rid['id'];
                $rid_info['date'] = isset($request['rid->date']) ? decodeDate($request['rid->date']) : $user->rid['date'];
                $user->rid = $rid_info;
            }

            if (isset($request['status'])) {
                $status = $request['status'];

                switch($status) {
                    case 'active':
                        $user->suspended = false;
                        $user->deleted_at = null;
                        break;
                    case 'suspended':
                        $user->suspended = true;
                        $user->deleted_at = date('Y-m-d');
                        break;
                    case 'deleted':
                        $user->suspended = false;
                        $user->deleted_at = !empty($request['deleted_at']) ? decodeDate($request['deleted_at']) : date('Y-m-d');
                        break;
                }
            }

            $user->save();

            if (isset($request['picture'])) {
                $file = $request['picture'];
                $filename = str_random(30);
                $file->move(gas_storage_path('app'), $filename);
                $user->picture = sprintf('app/%s', $filename);
                $user->save();
            }

            $user->updateContacts($request);
            return $user;
        });

        return $user;
    }

    public function picture($id)
    {
        $user = User::findOrFail($id);

        $path = gas_storage_path($user->picture);
        if (file_exists($path)) {
            return response()->download($path);
        }
        else {
            Log::error(_i('File non trovato: %s', $path));
            return '';
        }
    }

    public function destroy($id)
    {
        $user = DB::transaction(function () use ($id) {
            /*
                show() già si premura di verificare l'accesso all'utente
                richiesto, qui dobbiamo solo rafforzare l'accesso da
                amministratore se l'utente da eliminare non è un "amico"
            */
            $user = $this->show($id);

            if ($user->isFriend() == false) {
                $this->ensureAuth(['users.admin' => 'gas']);
            }

            if ($user->trashed())
                $user->forceDelete();
            else
                $user->delete();

            return $user;
        });

        return $user;
    }
}
