<?php

namespace App\Services;

use App\Exceptions\AuthException;
use Illuminate\Support\Str;

use Auth;
use Log;
use DB;
use Hash;

use App\Notifications\ApprovedMessage;
use App\Notifications\DeclinedMessage;
use App\User;
use App\Role;

class UsersService extends BaseService
{
    public function list($term = '', $all = false)
    {
		/*
			Esiste il caso di un utente che non può vedere l'elenco degli utenti
			ma può effettuare le consegne, e pertanto - per definizione - fare
			prenotazioni anche per conto terzi. Questa autorizzazione serve per
			permettere la ricerca nel pannello "Prenotazioni per Altri Utenti"
		*/
        $user = $this->ensureAuth(['users.admin' => 'gas', 'users.movements' => 'gas', 'users.view' => 'gas', 'supplier.shippings' => null]);

        $gas_id = $user->gas['id'];
        $query = User::with('roles')->where('parent_id', null)->where('gas_id', '=', $gas_id);

        if (!empty($term)) {
            $query->where(function ($query) use ($term) {
                $query->where('firstname', 'LIKE', "%$term%")->orWhere('lastname', 'LIKE', "%$term%");
            });
        }

        if ($all) {
            $query->filterEnabled();
		}

        $users = $query->sorted()->get();
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

    private function setCommonAttributes($user, $request)
    {
        $this->setIfSet($user, $request, 'username');
        $this->setIfSet($user, $request, 'firstname');
        $this->setIfSet($user, $request, 'lastname');
    }

    private function updatePassword($user, $request)
    {
        $user->password = Hash::make($request['password']);

        if (isset($request['enforce_password_change']) && $request['enforce_password_change'] == 'true') {
            $user->enforce_password_change = true;
        }

        $user->save();
    }

    public function store(array $request)
    {
        DB::beginTransaction();

        /*
            Gli utenti col permesso di agire sul multi-gas devono poter creare i
            nuovi utenti amministratori
        */
        $creator = $this->ensureAuth(['users.admin' => 'gas', 'gas.multi' => 'gas']);

        $user = new User();
        $this->setCommonAttributes($user, $request);
        $user->gas_id = $creator->gas->id;
        $user->member_since = date('Y-m-d', time());
        $user->password = Hash::make(Str::random(10));
        $user->save();

        if (isset($request['sendmail'])) {
            $user->addContact('email', $request['email']);
            $user->initialWelcome();
        }
        else {
            $this->updatePassword($user, $request);
        }

        DB::commit();
        return $user;
    }

    public function storeFriend(array $request)
    {
        DB::beginTransaction();

        $creator = $this->ensureAuth(['users.subusers' => 'gas']);
        if (isset($request['creator_id'])) {
            $creator = User::findOrFail($request['creator_id']);
        }

        $user = new User();
        $this->setCommonAttributes($user, $request);
        $user->parent_id = $creator->id;
        $user->gas_id = $creator->gas->id;
        $user->member_since = date('Y-m-d', time());
        $this->updatePassword($user, $request);
        DB::commit();
        return $user;
    }

    private function updateAccessType($id, $request)
    {
        $user = Auth::user();
        if (is_null($user)) {
            throw new AuthException(401);
        }

        if ($user->can('users.admin', $user->gas)) {
            $type = 1;
        }
        else if ($user->id == $id) {
            if ($user->can('users.self', $user->gas) == false) {
                /*
                    Anche laddove non sia concesso agli utenti il permesso di
                    cambiare la propria anagrafica, devono comunque poter cambiare
                    la propria password. Se quello è il solo parametro passato, la
                    nuova password viene salvata e la funzione ritorna
                    correttamente, altrimenti si testa il suddetto permesso
                */
                if (isset($request['password']) && !empty($request['password'])) {
                    $user = $this->show($id);

                    $this->transformAndSetIfSet($user, $request, 'password', function ($password) {
                        return Hash::make($password);
                    });

                    $user->enforce_password_change = false;
                    $user->save();
                    return $user;
                }

                throw new AuthException(403);
            }

            $type = 2;
        }
        else if ($user->can('users.subusers', $user->gas)) {
            $test = $this->show($id);
            if ($test->parent_id == $user->id) {
                $type = 2;
            }
            else {
                throw new AuthException(403);
            }
        }
        else {
            throw new AuthException(403);
        }

        return $type;
    }

    private function readRID($user, $request)
    {
        if ($user->gas->hasFeature('rid')) {
            $rid = $user->rid;
            $changed = false;

            if (isset($request['rid->iban'])) {
                $rid['iban'] = $request['rid->iban'];
                $changed = true;
            }

            if (isset($request['rid->id'])) {
                $rid['id'] = $request['rid->id'];
                $changed = true;
            }

            if (isset($request['rid->date'])) {
                $rid['date'] = decodeDate($request['rid->date']);
                $changed = true;
            }

            if ($changed) {
                $user->rid = $rid;
            }
        }
    }

    public function update($id, array $request)
    {
        $type = $this->updateAccessType($id, $request);
        if (is_object($type)) {
            return $type;
        }

        DB::beginTransaction();

        $user = $this->show($id);

        $this->setCommonAttributes($user, $request);
        $this->transformAndSetIfSet($user, $request, 'birthday', "decodeDate");
        $this->setIfSet($user, $request, 'taxcode');
        $this->transformAndSetIfSet($user, $request, 'family_members', 'enforceNumber');
        $this->setIfSet($user, $request, 'preferred_delivery_id');
        $this->setIfSet($user, $request, 'payment_method_id');

        if ($type == 1) {
            $user->enforce_password_change = (isset($request['enforce_password_change']) && $request['enforce_password_change'] == 'true');

            if (isset($request['status'])) {
                $user->setStatus($request['status'], $request['deleted_at'], $request['suspended_at']);
            }

            $this->transformAndSetIfSet($user, $request, 'member_since', "decodeDate");
            $this->setIfSet($user, $request, 'card_number');
        }
        else {
            $user->enforce_password_change = false;
        }

        if (isset($request['password']) && !empty($request['password'])) {
            $this->transformAndSetIfSet($user, $request, 'password', function ($password) {
                return Hash::make($password);
            });
        }

        $this->readRID($user, $request);
        $user->save();

        handleFileUpload($request, $user, 'picture');
        $user->updateContacts($request);

        DB::commit();
        return $user;
    }

    public function revisioned($id, $approved)
    {
        DB::beginTransaction();

        $user = $this->show($id);

        if ($approved) {
            $user->pending = false;
            $user->save();

            try {
                $user->notify(new ApprovedMessage());
            }
            catch(\Exception $e) {
                \Log::error('Impossibile notificare approvazione utente');
            }
        }
        else {
            try {
                $user->notify(new DeclinedMessage());
            }
            catch(\Exception $e) {
                \Log::error('Impossibile notificare non approvazione utente');
            }

            $user->forceDelete();
        }

        DB::commit();
    }

    public function picture($id)
    {
        $user = $this->show($id);
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
        DB::beginTransaction();

        $user = $this->show($id);

        if ($user->testUserAccess() == false) {
            $this->ensureAuth(['users.admin' => 'gas']);
        }

        if ($user->trashed()) {
            $user->forceDelete();
        }
        else {
            $user->delete();
        }

        DB::commit();
        return $user;
    }
}
