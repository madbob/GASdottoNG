<?php

namespace App;

use Auth;
use App;
use App\User;
use App\Permission;

/*
    Tutte le funzioni qui incluse assumono che l'autorizzazione a modificare
    i permessi sia concessa all'utente attuale.
    E' altamente consigliato invocarle solo all'interno di una transazione
    sul DB.
*/
trait AllowableTrait
{
    public function permissions()
    {
        return $this->morphMany('App\Permission', 'target');
    }

    public function permissionsCanBeModified()
    {
        $user = Auth::user();

        return $user->gas->userCan('gas.permissions') || $this->userCan('supplier.modify', $user);
    }

    private function permissionType($perm)
    {
        if ($perm == null) {
            return 0;
        } elseif ($perm->user_id == '*') {
            return 2;
        } else {
            return 1;
        }
    }

    private function superUserTest($user, $cache = null)
    {
        if ($cache == null) {
            $cache = App::make('PermissionsCache');
        }

        $u = null;

        if ($user != null) {
            if (is_object($user)) {
                $u = $user;
            } elseif (is_string($user)) {
                $u = User::find($user);
            } else {
                return 0;
            }
        } else {
            $u = Auth::user();
        }

        return $cache->get($u->id, 'gas.super', 'App\Gas', $u->gas->id);
    }

    private function normalizeUserId($user)
    {
        if ($user != null) {
            if (is_object($user)) {
                $id = $user->id;
            } elseif (is_string($user)) {
                $id = $user;
            } else {
                return 'none';
            }
        } else {
            $user = Auth::user();
            $id = $user->id;
        }

        return $id;
    }

    /*
        Restituisce
        0 se l'utente non e' autorizzato
        1 se l'utente e' autorizzato
        2 se tutti gli utenti (e dunque anche quello richiesto) sono autorizzati

        L'utente viene considerato autorizzato se una qualsiasi delle
        azioni richieste è concessa
    */
    public function userCan($action, $user = null)
    {
        $cache = App::make('PermissionsCache');

        $super = $this->superUserTest($user, $cache);
        if ($super != 0) {
            return $super;
        }

        $user_id = $this->normalizeUserId($user);
        $actions = explode('|', $action);

        foreach ($actions as $a) {
            $perm = $cache->get($user_id, $a, get_class($this), $this->id);
            if ($perm != 0) {
                return $perm;
            }
        }

        return 0;
    }

    /*
        Come userCan(), ma non prende in considerazione i permessi combinati
    */
    public function userReallyCan($action, $user = null)
    {
        $cache = App::make('PermissionsCache');

        $user_id = $this->normalizeUserId($user);
        $actions = explode('|', $action);

        foreach ($actions as $a) {
            $perm = $cache->get($user_id, $a, get_class($this), $this->id);
            if ($perm == 1) {
                return $perm;
            }
        }

        return 0;
    }

    /*
        Verifica che l'utente abbia almeno una autorizzazione del tipo
        richiesto, senza specificare un target specifico.
        Può essere invocata su qualsiasi oggetto che usa AllowableTrait
    */
    public function userHas($action, $user = null)
    {
        $super = $this->superUserTest($user);
        if ($super != 0) {
            return $super;
        }

        $user_id = $this->normalizeUserId($user);
        $perm = null;

        $actions = explode('|', $action);
        foreach ($actions as $a) {
            $perm = Permission::where('action', '=', $a)->where(function ($query) use ($user_id) {
                $query->where('user_id', '=', $user_id)->orWhere('user_id', '=', '*');
            })->first();

            if ($perm != null) {
                break;
            }
        }

        return $this->permissionType($perm);
    }

    /*
        Ritorna un array con tutti gli ID degli utenti che hanno
        l'autorizzazione del tipo richiesto sull'oggetto corrente.
        Nota bene: il valore speciale "*" (tutti gli utenti) non viene
        trasformato in un array con gli ID di tutti gli utenti ma viene
        lasciato tale
    */
    public function whoCan($action)
    {
        $ret = [];

        $actions = explode('|', $action);
        foreach ($actions as $a) {
            $perm = $this->permissions()->where('action', '=', $a)->get();
            foreach ($perm as $p) {
                $ret[$p->user_id] = $p->user_id;
            }
        }

        return $ret;
    }

    /*
        Simile a whoCan(), ma ritorna un risultato utile per costruire
        una interfaccia più comoda per l'utente
    */
    public function whoCanComplex($action)
    {
        $users = $this->whoCan($action);

        $current_users = User::count();
        $ret_users = [];
        $behaviour = '';

        if (array_key_exists('*', $users)) {
            $behaviour = 'all';
        } elseif (count($users) > ($current_users / 2)) {
            $excluded_users = User::whereNotIn('id', $users)->orderBy('lastname', 'asc')->get();
            foreach ($excluded_users as $eu) {
                $ret_users[] = (object) [
                    'id' => $eu->id,
                    'name' => $eu->printableName(),
                ];
            }

            $behaviour = 'except';
        } else {
            $included_users = User::whereIn('id', $users)->orderBy('lastname', 'asc')->get();
            foreach ($included_users as $iu) {
                $ret_users[] = (object) [
                    'id' => $iu->id,
                    'name' => $iu->printableName(),
                ];
            }

            $behaviour = 'selected';
        }

        return [
            'behaviour' => $behaviour,
            'users' => $ret_users,
        ];
    }

    /*
        Verifica che almeno un utente abbia il permesso richiesto / uno
        dei permessi richiesti sull'oggetto corrente
    */
    public function oneCan($action)
    {
        $perm = null;

        $actions = explode('|', $action);
        array_unshift($actions, 'gas.super');

        foreach ($actions as $a) {
            $perm = $this->permissions()->where('action', '=', $a)->where('target_id', '=', $this->id)->first();
            if ($perm != null) {
                break;
            }
        }

        return $this->permissionType($perm);
    }

    public function userPermit($action, $user)
    {
        $id = $this->normalizeUserId($user);

        $actions = explode('|', $action);
        foreach ($actions as $a) {
            /*
                Se abilito una azione per tutti gli utenti,
                prima elimino eventuali regole individuali
            */
            if ($id == '*') {
                $current = $this->whoCan($a);
                if (count($current) > 1 || array_key_exists('*', $current) == false) {
                    foreach ($current as $excluded) {
                        $this->userRevoke($a, $excluded);
                    }
                }
            }

            $t = $this->permissions()->firstOrCreate(['action' => $a, 'user_id' => $id]);
        }
    }

    public function userRevoke($action, $user)
    {
        $id = $this->normalizeUserId($user);

        $actions = explode('|', $action);
        foreach ($actions as $a) {
            /*
                Se l'autorizzazione è attualmente concessa a
                tutti, la revoco e la ri-concedo solo agli altri
                utenti
            */
            $current = $this->whoCan($a);
            if (array_key_exists('*', $current) == true) {
                $this->permissions()->where('action', '=', $a)->where('user_id', '=', '*')->delete();

                $current = User::where('id', '!=', $id)->get();
                foreach ($current as $included) {
                    $this->userPermit($a, $included);
                }
            } else {
                $this->permissions()->where('action', '=', $a)->where('user_id', '=', $id)->delete();
            }
        }
    }

    public function getPermissions()
    {
        $c = get_class($this);

        $all_permissions = Permission::allPermissions();
        foreach ($all_permissions as $class => $rules) {
            if ($class == $c) {
                return $rules;
            }
        }

        return [];
    }

    public function deletePermissions()
    {
        $this->permissions()->delete();
    }
}
