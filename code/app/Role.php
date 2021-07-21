<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

use DB;
use Auth;
use URL;
use Log;

class Role extends Model
{
    use GASModel, Cachable;

    private $targets = null;

    public function users()
    {
        return $this->belongsToMany('App\User')->orderBy('lastname', 'asc')->with('roles');
    }

    public function children()
    {
        return $this->hasMany('App\Role', 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo('App\Role', 'parent_id');
    }

    private static function recursiveSortedByHierarchy($roles, &$collection, &$ids)
    {
        foreach($roles as $role) {
            if (in_array($role->id, $ids) == false) {
                $collection->push($role);
                $ids[] = $role->id;
                self::recursiveSortedByHierarchy($role->children, $collection, $ids);
            }
        }
    }

    public static function sortedByHierarchy($limited = false)
    {
        $ret = new Collection();
        $ids = [];

        if ($limited) {
            $user = Auth::user();
            $roles = $user->roles;

            foreach($roles as $role) {
                self::recursiveSortedByHierarchy($role->children, $ret, $ids);
            }
        }
        else {
            $roles = self::where('parent_id', 0)->orderBy('name', 'asc')->get();
            self::recursiveSortedByHierarchy($roles, $ret, $ids);
        }

        return $ret;
    }

    public function printableHeader()
    {
        $ret = $this->printableName();

        $step = $this;
        $iterated = [$step->id];

        while(true) {
            $parent = $step->parent;

            if ($parent) {
                if (in_array($parent->id, $iterated)) {
                    Log::error('Recursive roles hierarchy');
                    break;
                }

                $ret = '&nbsp;&nbsp;&nbsp;&nbsp;' . $ret;
            }
            else {
                break;
            }

            $step = $parent;
            $iterated[] = $step->id;
        }

        $ret .= $this->headerIcons();

        return $ret;
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

    public function getAllClasses()
    {
        $ret = [];
        $permissions = self::allPermissions();

        foreach ($permissions as $class => $types) {
            foreach($types as $t => $label) {
                if ($this->enabledAction($t)) {
                    $ret[] = $class;
                    break;
                }
            }
        }

        return $ret;
    }

    public static function targetsByClass($class)
    {
        if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($class)))
            return $class::withTrashed()->orderBy('name', 'asc')->get();
        else
            return $class::orderBy('name', 'asc')->get();
    }

    public function getTargetsAttribute()
    {
        if (is_null($this->targets)) {
            $this->targets = new Collection();

            $classes = $this->getAllClasses();
            foreach($classes as $class) {
                $this->targets = $this->targets->merge(self::targetsByClass($class));
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
                if (isset($applies_cache[$class]) == false) {
                    $applies_cache[$class] = [];
                }

                if ($r->target_id == '*') {
                    if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($class)))
                        $objects = $class::withTrashed()->get();
                    else
                        $objects = $class::get();

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

    private function invalidateAppliesCache()
    {
        if (isset($this->applies_cache)) {
            unset($this->applies_cache);
            unset($this->applies_only_cache);
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
        if (is_null($class)) {
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
    public function applications($all = false, $exclude_trashed = false)
    {
        $this->appliesCache();

        if ($all)
            $cache_type = 'applies_cache';
        else
            $cache_type = 'applies_only_cache';

        $ret = new Collection();

        foreach($this->$cache_type as $class => $ids) {
            if ($exclude_trashed == false && in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($class)))
                $objs = $class::withTrashed()->whereIn('id', $ids)->get();
            else
                $objs = $class::whereIn('id', $ids)->get();

            $ret = $ret->merge($objs);
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
            if (is_null($obj) || $this->appliesOnly($obj))
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
            if (is_null($obj) || $this->appliesOnly($obj) == false)
                return;

            DB::table('attached_role_user')
                ->where('role_user_id', $this->pivot->id)
                ->where('target_id', $obj->id)
                ->where('target_type', get_class($obj))
                ->delete();
        }

        $this->invalidateAppliesCache();
    }

    public function enabledAction($action)
    {
        $actions = explode(',', $this->actions);
        return (in_array($action, $actions));
    }

    public function enableAction($action)
    {
        if ($this->enabledAction($action) == false) {
            $this->actions .= ',' . $action;
            $this->save();

            /*
                Se attivo un permesso che ha un solo target (di solito: il GAS),
                attacco quest'ultimo direttamente a tutti gli utenti coinvolti
            */
            $class = self::classByRule($action);
            if ($class::count() == 1) {
                $only_target = $class::first();

                foreach($this->users as $user) {
                    $urole = $user->roles()->where('roles.id', $this->id)->first();
                    if ($urole)
                        $urole->attachApplication($only_target);
                }
            }
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
            if (is_null($subject)) {
                $users = $br->users;
                if ($users->isEmpty() == false)
                    return true;
                else
                    return false;
            }
            else {
                $users = $br->usersByTarget($subject);
                if ($users->isEmpty() == false)
                    return true;
            }
        }

        return false;
    }

    public static function everybodyCan($permission, $subject = null)
    {
        $ret = new Collection();

        $basic_roles = self::havingAction($permission);
        foreach($basic_roles as $br) {
            $users = $br->users;

            if ($subject != null)
                $users = $br->usersByTarget($subject);

            $ret = $ret->merge($users);
        }

        return $ret->unique('id');
    }

    public static function allPermissions()
    {
        return [
            'App\Gas' => [
                'gas.access' => _i('Accesso consentito anche in manutenzione'),
                'gas.permissions' => _i('Modificare tutti i permessi'),
                'gas.config' => _i('Modificare le configurazioni del GAS'),
                'supplier.add' => _i('Creare nuovi fornitori'),
                'supplier.book' => _i('Effettuare ordini'),
                'supplier.view' => _i('Vedere tutti i fornitori'),
                'order.view' => _i('Vedere tutti gli ordini'),
                'users.self' => _i('Modificare la propria anagrafica'),
                'users.admin' => _i('Amministrare gli utenti'),
                'users.view' => _i('Vedere tutti gli utenti'),
                'users.subusers' => _i('Avere sotto-utenti con funzioni limitate'),
                'users.movements' => _i('Amministrare i movimenti contabili degli utenti'),
                'movements.admin' => _i('Amministrare tutti i movimenti contabili'),
                'movements.view' => _i('Vedere i movimenti contabili'),
                'movements.types' => _i('Amministrare i tipi dei movimenti contabili'),
                'categories.admin' => _i('Amministrare le categorie'),
                'measures.admin' => _i('Amministrare le unità di misura'),
                'gas.statistics' => _i('Visualizzare le statistiche'),
                'notifications.admin' => _i('Amministrare le notifiche'),
                'gas.multi' => _i('Amministrare i GAS su questa istanza'),
            ],
            'App\Supplier' => [
                'supplier.modify' => _i('Modificare i fornitori assegnati'),
                'supplier.orders' => _i('Aprire e modificare ordini'),
                'supplier.shippings' => _i('Effettuare le consegne'),
                'supplier.movements' => _i('Amministrare i movimenti contabili'),
            ],
        ];
    }

    public static function allTargets()
    {
        $targets = [];

        $permissions = self::allPermissions();
        foreach ($permissions as $class => $types) {
            if (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($class)))
                $all = $class::withTrashed()->orderBy('name', 'asc')->get();
            else
                $all = $class::orderBy('name', 'asc')->get();

            foreach ($all as $subject) {
                $targets[] = $subject;
            }
        }

        return $targets;
    }

    public function enabledClass($class)
    {
        return in_array($class, $this->getAllClasses());
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
