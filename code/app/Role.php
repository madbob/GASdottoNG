<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

use DB;
use URL;

use App\GASModel;
use App\User;

class Role extends Model
{
    use GASModel;

    private $targets = null;

    public function users()
    {
        return $this->belongsToMany('App\User')->orderBy('lastname', 'asc')->with('roles');
    }

    public function usersByTarget($target)
    {
        $role = $this;

        $user_ids = DB::table('users')->join('role_user', function($join) use ($role) {
            $join->on('user_id', '=', 'users.id');
            $join->where('role_id', '=', $role->id);
        })->join('attached_role_user', function($join) use ($target) {
            $join->on('role_user_id', '=', 'role_user.id');
            $join->where('target_type', '=', get_class($target))->where('target_id', '=', $target->id);
        })->pluck('users.id');

        return User::whereIn('id', $user_ids)->get();
    }

    private function getAllClasses()
    {
        $ret = [];
        $permissions = self::allPermissions();

        foreach ($permissions as $class => $types) {
            $found = false;

            foreach($types as $t => $label) {
                if ($this->enabledAction($t)) {
                    $ret[] = $class;
                    break;
                }
            }
        }

        return $ret;
    }

    public function getTargetsAttribute()
    {
        if ($this->targets == null) {
            $this->targets = new Collection();

            $classes = $this->getAllClasses();
            foreach($classes as $class) {
                $all = $class::orderBy('name', 'asc')->get();
                $this->targets = $this->targets->merge($all);
            }
        }

        return $this->targets;
    }

    private function appliesCache()
    {
        if (isset($this->applies_cache) == false) {
            $applies_cache = [];
            $applies_only_cache = [];

            $rules = DB::table('attached_role_user')->where('role_user_id', $this->pivot->id)->get();
            foreach($rules as $r) {
                $class = $r->target_type;
                if (isset($applies_cache[$class]) == false)
                    $applies_cache[$class] = [];

                if ($r->target_id == '*') {
                    $objects = $class::withTrashed()->get();
                    foreach($objects as $o)
                        $applies_cache[$class][] = $o->id;
                }
                else {
                    $applies_cache[$class][] = $r->target_id;
                    $applies_only_cache[$class][] = $r->target_id;
                }
            }

            $this->applies_cache = $applies_cache;
            $this->applies_only_cache = $applies_only_cache;
        }
    }

    private function testApplication($obj, $cache_type)
    {
        $this->appliesCache();

        $class = get_class($obj);
        if (!isset($this->$cache_type[$class])) {
            $proxies = $obj->getPermissionsProxies();
            if ($proxies != null) {
                foreach($proxies as $proxy) {
                    $test = $this->applies($proxy);
                    if ($test)
                        return true;
                }
            }

            return false;
        }
        else {
            return (isset($this->$cache_type[$class]) && array_search($obj->id, $this->$cache_type[$class]) !== false);
        }
    }

    /*
        Questa funzione va chiamata solo sugli oggetti Role restituiti da
        User::roles(), in quanto si applica solo sull'istanza del ruolo
        assegnata ad uno specifico utente
    */
    public function applies($obj)
    {
        return $this->testApplication($obj, 'applies_cache');
    }

    /*
        Questa funzione va chiamata solo sugli oggetti Role restituiti da
        User::roles(), in quanto si applica solo sull'istanza del ruolo
        assegnata ad uno specifico utente
    */
    public function appliesOnly($obj)
    {
        return $this->testApplication($obj, 'applies_only_cache');
    }

    /*
        Questa funzione va chiamata solo sugli oggetti Role restituiti da
        User::roles(), in quanto si applica solo sull'istanza del ruolo
        assegnata ad uno specifico utente
    */
    public function appliesAll($class = null)
    {
        if ($class == null) {
            $ret = true;

            $classes = $this->getAllClasses();
            foreach($classes as $class) {
                $ret = $ret && $this->appliesAll($class);
            }

            return $ret;
        }
        else {
            return DB::table('attached_role_user')
                ->where('role_user_id', $this->pivot->id)
                ->where('target_type', $class)
                ->where('target_id', '*')
                ->count();
        }
    }

    /*
        Questa funzione va chiamata solo sugli oggetti Role restituiti da
        User::roles(), in quanto si applica solo sull'istanza del ruolo
        assegnata ad uno specifico utente
    */
    public function applications($all = false)
    {
        $this->appliesCache();

        if ($all)
            $cache_type = 'applies_cache';
        else
            $cache_type = 'applies_only_cache';

        $ret = [];

        foreach($this->$cache_type as $class => $ids) {
            foreach($ids as $id) {
                $obj = $class::find($id);
                if ($obj == null)
                    continue;

                $ret[] = $obj;
            }
        }

        return $ret;
    }

    /*
        Questa funzione va chiamata solo sugli oggetti Role restituiti da
        User::roles(), in quanto si applica solo sull'istanza del ruolo
        assegnata ad uno specifico utente.

        Se $obj è un oggetto, il permesso viene applicato solo su quello.
        Se è una stringa viene applicato un permesso speciale valevole per tutti
        i soggetti della classe specificata
    */
    public function attachApplication($obj)
    {
        $now = date('Y-m-d G:i:s');

        if (is_string($obj)) {
            if ($this->appliesAll($obj))
                return;

            $obj_class = $obj;
            $obj_id = '*';
        }
        else {
            if ($obj == null || $this->appliesOnly($obj))
                return;

            $obj_class = get_class($obj);
            $obj_id = $obj->id;
        }

        DB::table('attached_role_user')->insert([
            'role_user_id' => $this->pivot->id,
            'target_id' => $obj_id,
            'target_type' => $obj_class,
            'created_at' => $now,
            'updated_at' => $now
        ]);
    }

    /*
        Questa funzione va chiamata solo sugli oggetti Role restituiti da
        User::roles(), in quanto si applica solo sull'istanza del ruolo
        assegnata ad uno specifico utente.

        Se $obj è un oggetto, il permesso viene revocato solo su quello.
        Se è una stringa viene revocato ogni permesso valevole per tutti i
        soggetti della classe specificata (inclusi quelli speciali, cfr.
        attachApplication())
    */
    public function detachApplication($obj)
    {
        if (is_string($obj)) {
            DB::table('attached_role_user')
                ->where('role_user_id', $this->pivot->id)
                ->where('target_id', '*')
                ->where('target_type', $obj)
                ->delete();
        }
        else {
            if ($obj == null || $this->appliesOnly($obj) == false)
                return;

            DB::table('attached_role_user')
                ->where('role_user_id', $this->pivot->id)
                ->where('target_id', $obj->id)
                ->where('target_type', get_class($obj))
                ->delete();
        }
    }

    public function enabledAction($action)
    {
        $actions = explode(',', $this->actions);
        return (array_search($action, $actions) !== false);
    }

    public function enableAction($action)
    {
        if ($this->enabledAction($action) == false) {
            $this->actions .= ',' . $action;
            $this->save();
        }
    }

    public function disableAction($action)
    {
        $new_actions = [];
        $actions = explode(',', $this->actions);
        foreach($actions as $a) {
            if ($a == $action)
                continue;
            $new_actions[] = $a;
        }

        $this->actions = join(',', $new_actions);
        $this->save();
    }

    public static function havingAction($action)
    {
        return Role::where('actions', 'LIKE', "%$action%")->get();
    }

    public static function someone($permission, $subject = null)
    {
        $basic_roles = self::havingAction($permission);
        foreach($basic_roles as $br) {
            $users = $br->users;

            if ($subject == null && $users->isEmpty() == false)
                return true;

            $users = $br->usersByTarget($subject);
            if ($users->isEmpty() == false)
                return true;
        }

        return false;
    }

    public static function allPermissions()
    {
        return [
            'App\Gas' => [
                'gas.access' => 'Accesso consentito anche in manutenzione',
                'gas.permissions' => 'Modificare tutti i permessi',
                'gas.config' => 'Modificare le configurazioni del GAS',
                'supplier.add' => 'Creare nuovi fornitori',
                'supplier.book' => 'Effettuare ordini',
                'users.admin' => 'Amministrare le anagrafiche degli utenti',
                'users.view' => 'Vedere tutti gli utenti',
                'users.movements' => 'Amministrare i movimento contabili degli utenti',
                'movements.admin' => 'Amministrare tutti i movimenti contabili',
                'movements.view' => 'Vedere i movimenti contabili',
                'movements.types' => 'Amministrare i tipi dei movimenti contabili',
                'categories.admin' => 'Amministrare le categorie',
                'measures.admin' => 'Amministrare le unità di misura',
                'gas.statistics' => 'Visualizzare le statistiche',
                'notifications.admin' => 'Amministrare le notifiche',
            ],
            'App\Supplier' => [
                'supplier.modify' => 'Modificare i fornitori assegnati',
                'supplier.orders' => 'Aprire e modificare ordini',
                'supplier.shippings' => 'Effettuare le consegne',
            ],
        ];
    }

    public static function allTargets()
    {
        $targets = [];

        $permissions = self::allPermissions();
        foreach ($permissions as $class => $types) {
            $all = $class::orderBy('name', 'asc')->get();
            foreach ($all as $subject) {
                $targets[] = $subject;
            }
        }

        return $targets;
    }

    public static function classByRule($rule_id)
    {
        $all_permissions = self::allPermissions();
        foreach ($all_permissions as $class => $rules) {
            foreach ($rules as $identifier => $name) {
                if ($rule_id == $identifier) {
                    return $class;
                }
            }
        }

        return null;
    }

    public static function rolesByClass($asked_class)
    {
        $roles = [];

        $all_permissions = self::allPermissions();

        foreach (Role::all() as $role) {
            foreach ($all_permissions as $class => $rules) {
                if ($class == $asked_class) {
                    foreach ($rules as $identifier => $name) {
                        if ($role->enabledAction($identifier)) {
                            $roles[] = $role;
                            break;
                        }
                    }
                }
            }
        }

        return $roles;
    }
}
