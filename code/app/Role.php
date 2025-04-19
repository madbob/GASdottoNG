<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use MadBob\Larastrap\Integrations\AutoReadsFields;
use MadBob\Larastrap\Integrations\AutoReadOperation;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Role extends Model implements AutoReadsFields
{
    use Cachable, GASModel, HasFactory;

    private $real_targets = null;

    private $applies_cache = null;

    private $applies_only_cache = null;

    public function users(): BelongsToMany
    {
        return $this->belongsToMany('App\User')->orderBy('lastname', 'asc')->with('roles');
    }

    public function children(): HasMany
    {
        return $this->hasMany('App\Role', 'parent_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo('App\Role', 'parent_id');
    }

    public function isEnabled()
    {
        if ($this->system) {
            foreach (systemParameters('Roles') as $ref) {
                if ($this->identifier == $ref->identifier()) {
                    return $ref->enabled();
                }
            }
        }

        return true;
    }

    private static function recursiveSortedByHierarchy($roles, &$collection, &$ids)
    {
        foreach ($roles as $role) {
            if ($role->isEnabled() && in_array($role->id, $ids) == false) {
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

            foreach ($roles as $role) {
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

        while (true) {
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

        $user_ids = DB::table('users')->join('role_user', function ($join) use ($role) {
            $join->on('user_id', '=', 'users.id');
            $join->where('role_id', '=', $role->id);
        })->join('attached_role_user', function ($join) use ($target) {
            $join->on('role_user_id', '=', 'role_user.id');
            $join->where('target_type', '=', get_class($target))->where('target_id', '=', $target->id);
        })->pluck('users.id');

        return User::whereIn('id', $user_ids)->get();
    }

    public function getAllClasses()
    {
        $ret = [];
        $permissions = allPermissions();

        foreach ($permissions as $class => $types) {
            foreach (array_keys($types) as $t) {
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
        if (is_null($this->real_targets)) {
            $this->real_targets = new Collection();

            $classes = $this->getAllClasses();
            foreach ($classes as $class) {
                $this->real_targets = $this->real_targets->merge($class::tAll());
            }
        }

        return $this->real_targets;
    }

    private function appliesCache()
    {
        if (is_null($this->applies_cache)) {
            $applies_cache = [];
            $applies_only_cache = [];

            $rules = DB::table('attached_role_user')->where('role_user_id', $this->getRelationValue('pivot')->id)->get();
            foreach ($rules as $r) {
                $class = $r->target_type;
                if (isset($applies_cache[$class]) == false) {
                    $applies_cache[$class] = [];
                }

                if ($r->target_id == '*') {
                    $objects = $class::tAll();
                    foreach ($objects as $o) {
                        $applies_cache[$class][] = $o->id;
                    }
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
        $this->applies_cache = null;
        $this->applies_only_cache = null;
    }

    private function testApplication($obj, $cache_type)
    {
        $this->appliesCache();

        $class = get_class($obj);
        if (! isset($this->$cache_type[$class])) {
            $proxies = $obj->getPermissionsProxies();
            if ($proxies != null) {
                foreach ($proxies as $proxy) {
                    $test = $this->applies($proxy);
                    if ($test) {
                        return true;
                    }
                }
            }

            return false;
        }
        else {
            return isset($this->$cache_type[$class]) && array_search($obj->id, $this->$cache_type[$class]) !== false;
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
        assegnata ad uno specifico utente.
        Verifica se il ruolo si applica a tutti i soggetti della classe
        specificata (o più in generale a tutti i soggetti di tutte le classi
        disponibili)
    */
    public function appliesAll($class = null)
    {
        if (is_null($class)) {
            $ret = true;

            $classes = $this->getAllClasses();
            foreach ($classes as $class) {
                $ret = $ret && $this->appliesAll($class);
            }

            return $ret;
        }
        else {
            return DB::table('attached_role_user')
                ->where('role_user_id', $this->getRelationValue('pivot')->id)
                ->where('target_type', $class)
                ->where('target_id', '*')
                ->count();
        }
    }

    /*
        Questa funzione va chiamata solo sugli oggetti Role restituiti da
        User::roles(), in quanto si applica solo sull'istanza del ruolo
        assegnata ad uno specifico utente.
        Restituisce l'elenco dei soggetti cui il ruolo si applica
    */
    public function applications($all = false, $exclude_trashed = false, $target_class = null)
    {
        $this->appliesCache();

        if ($all) {
            $cache_type = 'applies_cache';
        }
        else {
            $cache_type = 'applies_only_cache';
        }

        $ret = new Collection();

        foreach ($this->$cache_type as $class => $ids) {
            if ($target_class && $target_class != $class) {
                continue;
            }

            if ($exclude_trashed == false && hasTrait($class, SoftDeletes::class)) {
                $objs = $class::withTrashed()->whereIn('id', $ids)->get();
            }
            else {
                $objs = $class::whereIn('id', $ids)->get();
            }

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
            if ($this->appliesAll($obj)) {
                return;
            }

            $obj_class = $obj;
            $obj_id = '*';
        }
        else {
            if (is_null($obj) || $this->appliesOnly($obj)) {
                return;
            }

            $obj_class = get_class($obj);
            $obj_id = $obj->id;
        }

        DB::table('attached_role_user')->insert([
            'role_user_id' => $this->getRelationValue('pivot')->id,
            'target_id' => $obj_id,
            'target_type' => $obj_class,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->invalidateAppliesCache();
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
                ->where('role_user_id', $this->getRelationValue('pivot')->id)
                ->where('target_id', '*')
                ->where('target_type', $obj)
                ->delete();
        }
        else {
            if (is_null($obj) || $this->appliesOnly($obj) == false) {
                return;
            }

            DB::table('attached_role_user')
                ->where('role_user_id', $this->getRelationValue('pivot')->id)
                ->where('target_id', $obj->id)
                ->where('target_type', get_class($obj))
                ->delete();
        }

        $this->invalidateAppliesCache();
    }

    public function enabledAction($action)
    {
        $actions = explode(',', $this->actions);

        return in_array($action, $actions);
    }

    public function mandatoryAction($action)
    {
        if (in_array($action, ['gas.access', 'gas.permissions'])) {
            $roles = Role::havingAction($action);
            if ($roles->count() == 1 && $roles->first()->id == $this->id) {
                return true;
            }
        }

        return false;
    }

    public static function havingAction($action)
    {
        return Role::where('actions', 'LIKE', "%$action%")->get();
    }

    public function enabledClass($class)
    {
        return in_array($class, $this->getAllClasses());
    }

    /******************************************************** AutoReadsFields */

    public function autoreadField($name, $request): AutoReadOperation
    {
        switch ($name) {
            case 'actions':
                $this->actions = implode(',', $request->input('actions') ?? []);

                return AutoReadOperation::Managed;
        }

        return AutoReadOperation::Auto;
    }
}
