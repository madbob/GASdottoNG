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
    public function list($term = '', $all = false, $involved = null)
    {
        $user = $this->ensureAuth(['users.admin' => 'gas', 'users.view' => 'gas']);
        $gas_id = $user->gas['id'];
        $query = User::with('roles')->where('parent_id', null)->where('gas_id', '=', $gas_id);

        if (!empty($term)) {
            $query->where(function ($query) use ($term) {
                $query->where('firstname', 'LIKE', "%$term%")->orWhere('lastname', 'LIKE', "%$term%");
            });
        }

        if (is_null($involved) == false) {
            $query->filterEnabled();
            $query->whereIn('id', $involved);
        }
        else {
            if ($all)
                $query->filterEnabled();
        }

        $users = $query->orderBy('lastname', 'asc')->get();
        return $users;
    }

    public function show($id)
    {
        $user = Auth::user();
        if (is_null($user)) {
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
        $test = User::withTrashed()->where('username', $username)->first();
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

        if (isset($request['enforce_password_change']) && $request['enforce_password_change'] == 'true')
            $user->enforce_password_change = true;

        DB::transaction(function () use ($user) {
            $user->save();
        });

        return $user;
    }

    public function storeFriend(array $request)
    {
        $creator = $this->ensureAuth(['users.subusers' => 'gas']);
        if (isset($request['creator_id'])) {
            $creator = User::find($request['creator_id']);
        }

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

        if (isset($request['enforce_password_change']) && $request['enforce_password_change'] == 'true')
            $user->enforce_password_change = true;

        DB::transaction(function () use ($user, $creator) {
            $user->save();

            $user_role = $creator->gas->roles['user'];
            $friend_role = $creator->gas->roles['friend'];

            if ($user_role != $friend_role) {
                $role = Role::find($friend_role);
                if ($role) {
                    $user->roles()->detach();
                    $role_id = normalizeId($role);
                    $user->addRole($role_id, $creator->gas);
                }
            }
        });

        return $user;
    }

    public function update($id, array $request)
    {
        $user = Auth::user();
        if (is_null($user)) {
            throw new AuthException(401);
        }

        if ($user->can('users.admin', $user->gas)) {
            $type = 1;
        }
        else if ($user->id == $id) {
            /*
                Anche laddove non sia concesso agli utenti il permesso di
                cambiare la propria anagrafica, devono comunque poter cambiare
                la propria password. Se quello è il solo parametro passato, la
                nuova password viene salvata e la funzione ritorna
                correttamente, altrimenti si testa il suddetto permesso
            */
            if(isset($request['password']) && !empty($request['password']) && count($request) == 1) {
                $user = $this->show($id);

                $this->transformAndSetIfSet($user, $request, 'password', function ($password) {
                    return Hash::make($password);
                });

                $user->save();
                return $user;
            }

            if ($user->can('users.self', $user->gas) == false) {
                throw new AuthException(403);
            }

            $type = 2;
        }
        else {
            throw new AuthException(403);
        }

        $user = DB::transaction(function () use ($id, $request, $type) {
            $user = $this->show($id);

            if (isset($request['username'])) {
                $username = $request['username'];
                $test = User::withTrashed()->where('id', '!=', $user->id)->where('username', $username)->first();
                if ($test != null) {
                    throw new IllegalArgumentException(_i('Username già assegnato'), 'username');
                }
            }

            if (isset($request['card_number'])) {
                $card_number = $request['card_number'];
                if (!empty($card_number)) {
                    $test = User::where('id', '!=', $user->id)->where('gas_id', $user->gas_id)->where('card_number', $card_number)->first();
                    if ($test != null) {
                        throw new IllegalArgumentException(_i('Numero tessera già assegnato'), 'card_number');
                    }
                }
            }

            $this->setIfSet($user, $request, 'username');
            $this->setIfSet($user, $request, 'firstname');
            $this->setIfSet($user, $request, 'lastname');
            $this->transformAndSetIfSet($user, $request, 'birthday', "decodeDate");
            $this->setIfSet($user, $request, 'taxcode');
            $this->transformAndSetIfSet($user, $request, 'family_members', 'enforceNumber');
            $this->setIfSet($user, $request, 'preferred_delivery_id');
            $this->setIfSet($user, $request, 'payment_method_id');

            if ($type == 1) {
                if (isset($request['enforce_password_change']) && $request['enforce_password_change'] == 'true') {
                    $user->enforce_password_change = true;
                }
                else {
                    $user->enforce_password_change = false;
                }

                if (isset($request['status'])) {
                    $user->setStatus($request['status'], $request['deleted_at'], $request['suspended_at']);
                }

                $this->transformAndSetIfSet($user, $request, 'member_since', "decodeDate");
                $this->setIfSet($user, $request, 'card_number');
            }
            else {
                $user->enforce_password_change = false;
            }

            if(isset($request['password']) && !empty($request['password'])) {
                $this->transformAndSetIfSet($user, $request, 'password', function ($password) {
                    return Hash::make($password);
                });
            }

            if($user->gas->hasFeature('rid')) {
                $rid_info['iban'] = $request['rid->iban'] ?? $user->rid['iban'];
                $rid_info['id'] = $request['rid->id'] ?? $user->rid['id'];
                $rid_info['date'] = isset($request['rid->date']) ? decodeDate($request['rid->date']) : $user->rid['date'];
                $user->rid = $rid_info;
            }

            $user->save();

            if (isset($request['picture'])) {
                saveFile($request['picture'], $user, 'picture');
            }

            $user->updateContacts($request);
            return $user;
        });

        return $user;
    }

    public function picture($id)
    {
        $user = User::findOrFail($id);
        return downloadFile($user, 'picture');
    }

    public function notifications($id, $suppliers)
    {
        $user = $this->show($id);

        if ($user->testUserAccess() == false) {
            $this->ensureAuth(['users.admin' => 'gas']);
        }

        $user->suppliers()->sync($suppliers);
    }

    public function destroy($id)
    {
        $user = DB::transaction(function () use ($id) {
            $user = $this->show($id);

            if ($user->testUserAccess() == false) {
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
