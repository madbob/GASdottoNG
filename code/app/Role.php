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

    public function getTargetsAttribute()
    {
        if ($this->targets == null) {
            $this->targets = new Collection();

            $permissions = self::allPermissions();

            foreach ($permissions as $class => $types) {
                $found = false;

                foreach($types as $t => $label) {
                    if ($this->enabledAction($t)) {
                        $found = true;
                        break;
                    }
                }

                if ($found == true) {
                    $all = $class::orderBy('name', 'asc')->get();
                    $this->targets = $this->targets->merge($all);
                }
            }
        }

        return $this->targets;
    }

    /*
        Questa funzione va chiamata solo sugli oggetti Role restituiti da
        User::roles(), in quanto si applica solo sull'istanza del ruolo
        assegnata ad uno specifico utente
    */
    public function applies($obj)
    {
        if (isset($this->applies_cache) == false) {
            $applies_cache = [];
            $rules = DB::table('attached_role_user')->where('role_user_id', $this->pivot->id)->get();
            foreach($rules as $r) {
                $class = $r->target_type;
                if (isset($applies_cache[$class]) == false)
                    $applies_cache[$class] = [];
                $applies_cache[$class][] = $r->target_id;
            }

            $this->applies_cache = $applies_cache;
        }

        $class = get_class($obj);
        if (!isset($this->applies_cache[$class])) {
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
            return (isset($this->applies_cache[$class]) && array_search($obj->id, $this->applies_cache[$class]) !== false);
        }
    }

    /*
        Questa funzione va chiamata solo sugli oggetti Role restituiti da
        User::roles(), in quanto si applica solo sull'istanza del ruolo
        assegnata ad uno specifico utente
    */
    public function applications()
    {
        $ret = [];

        $attached_objects = DB::table('attached_role_user')->where('role_user_id', $this->pivot->id)->get();
        foreach($attached_objects as $ao) {
            $class = $ao->target_type;

            $obj = $class::find($ao->target_id);
            if ($obj == null)
                continue;

            $ret[] = $obj;
        }

        return $ret;
    }

    /*
        Questa funzione va chiamata solo sugli oggetti Role restituiti da
        User::roles(), in quanto si applica solo sull'istanza del ruolo
        assegnata ad uno specifico utente
    */
    public function attachApplication($obj)
    {
        if ($obj == null || $this->applies($obj))
            return;

        $now = date('Y-m-d G:i:s');

        DB::table('attached_role_user')->insert([
            'role_user_id' => $this->pivot->id,
            'target_id' => $obj->id,
            'target_type' => get_class($obj),
            'created_at' => $now,
            'updated_at' => $now
        ]);
    }

    /*
        Questa funzione va chiamata solo sugli oggetti Role restituiti da
        User::roles(), in quanto si applica solo sull'istanza del ruolo
        assegnata ad uno specifico utente
    */
    public function detachApplication($obj)
    {
        if ($obj == null || $this->applies($obj) == false)
            return;

        DB::table('attached_role_user')->where('role_user_id', $this->pivot->id)->where('target_id', $obj->id)->where('target_type', get_class($obj))->delete();
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
                'users.admin' => 'Amministrare gli utenti',
                'users.view' => 'Vedere tutti gli utenti',
                'movements.admin' => 'Amministrare i movimenti contabili',
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
