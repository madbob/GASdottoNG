<?php

namespace App\Services;

use App\Exceptions\AuthException;
use App\Exceptions\IllegalArgumentException;
use App\User;
use Auth;
use Log;
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
        if ($user->can('users.admin', $user->gas) || $user->can('users.view', $user->gas)) {
            return $user;
        }

        throw new AuthException(403);
    }

    /*
        Ritorna:
        - 1 se l'utente ha permessi di amministrazione
        - 2 se l'utente richiesto è l'utente corrente
    */
    private function ensureAuthAdminOrOwner($id)
    {
        $this->ensureAuth();

        $user = Auth::user();

        if ($user->can('users.admin', $user->gas))
            return 1;
        else if ($user->id == $id)
            return 2;

        throw new AuthException(403);
        return 0;
    }

    private function ensureAuthAdmin()
    {
        $this->ensureAuth();

        $user = Auth::user();
        if ($user->can('users.admin', $user->gas)) {
            return;
        }

        throw new AuthException(403);
    }

    public function listUsers($term = '', $all = false)
    {
        $user = $this->ensureAuthAdminOrView();
        $gasID = $user->gas['id'];

        $query = User::with('roles')->where('gas_id', '=', $gasID);

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
        $this->ensureAuthAdminOrView();

        return User::withTrashed()->findOrFail($id);
    }

    public function destroy($id)
    {
        $this->ensureAuthAdmin();

        $user = DB::transaction(function () use ($id) {
            $user = $this->show($id);

            if ($user->trashed())
                $user->forceDelete();
            else
                $user->delete();

            return $user;
        });

        return $user;
    }

    public function update($id, array $request)
    {
        $type = $this->ensureAuthAdminOrOwner($id);

        $user = DB::transaction(function () use ($id, $request, $type) {
            $user = $this->show($id);

            if (isset($request['username'])) {
                $username = $request['username'];
                $test = User::where('id', '!=', $user->id)->where('username', $username)->first();
                if ($test != null) {
                    throw new IllegalArgumentException('Username già assegnato', 'username');
                }
            }

            if (isset($request['card_number'])) {
                $card_number = $request['card_number'];
                $test = User::where('id', '!=', $user->id)->where('gas_id', $user->gas_id)->where('card_number', $card_number)->first();
                if ($test != null) {
                    throw new IllegalArgumentException('Numero tessera già assegnato', 'card_number');
                }
            }

            $this->setIfSet($user, $request, 'username');
            $this->setIfSet($user, $request, 'firstname');
            $this->setIfSet($user, $request, 'lastname');
            $this->transformAndSetIfSet($user, $request, 'birthday', "decodeDate");
            $this->setIfSet($user, $request, 'taxcode');
            $this->setIfSet($user, $request, 'family_members');
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

            if(!empty($user->gas->rid_name)) {
                $this->transformAndSetIfSet($user, $request, 'iban', function ($iban) {
                    return strtoupper(str_replace(' ', '', $iban));
                });

                $this->transformAndSetIfSet($user, $request, 'sepa_subscribe', "decodeDate");
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

        $username = $request['username'];
        $test = User::where('username', $username)->first();
        if ($test != null) {
            throw new IllegalArgumentException('Username già assegnato', 'username');
        }

        $user = new User();
        $user->id = $username;
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

    public function picture($id)
    {
        $this->ensureAuth();
        $user = User::findOrFail($id);

        $path = gas_storage_path($user->picture);
        if (file_exists($path)) {
            return response()->download($path);
        }
        else {
            Log::error('File non trovato: ' . $path);
            return '';
        }
    }
}
