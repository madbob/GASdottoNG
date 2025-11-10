<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use Log;

use App\Role;

trait RoleableTrait
{
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->orderBy('name', 'asc')->withPivot('id');
    }

    public function getManagedRolesAttribute()
    {
        /*
            Se l'utente ha il permesso di gestire tutti i permessi, gli si
            concede di manipolare tutti i ruoli indipendentemente dalla
            gerarchia
        */
        $all_roles = $this->can('gas.permissions', $this->gas);

        return Role::sortedByHierarchy($all_roles === false);
    }

    public function checkRoleTargets($role)
    {
        $test = $this->roles()->where('roles.id', $role->id)->first();
        if ($test) {
            $classes = $role->getAllClasses();
            foreach ($classes as $class) {
                $targets = $test->applications(true, false, $class);
                if ($targets->isEmpty()) {
                    return false;
                }
            }

            return true;
        }
        else {
            return false;
        }
    }

    /*
        Reminder: i ruoli vengono tenuti nella cache locale della classe Role.
        Scrivendo i test, tenere in considerazione che non vengono
        necessariamente valutati in tempo reale
    */
    public function addRole($role, $assigned)
    {
        $role_id = normalizeId($role);

        $test = $this->roles()->where('roles.id', $role_id)->first();
        if (is_null($test)) {
            $this->roles()->attach($role_id);
            $test = $this->roles()->where('roles.id', $role_id)->first();
        }

        if (is_null($test)) {
            Log::error('Impossibile aggiungere ruolo ' . $role_id . ' a ' . $this->id);
        }
        else {
            if ($assigned) {
                $test->attachApplication($assigned);
            }
        }

        return $test;
    }

    public function removeRole($role, $assigned)
    {
        $role_id = normalizeId($role);

        $test = $this->roles()->where('roles.id', $role_id)->first();
        if (is_null($test)) {
            return;
        }

        if ($assigned) {
            $test->detachApplication($assigned);
            if ($test->applications(true)->isEmpty()) {
                $this->roles()->detach($role_id);
            }
        }
        else {
            $this->roles()->detach($role_id);
        }
    }

    public function targetsByAction($actions, $exclude_trashed = true)
    {
        $actions = explode(',', $actions);
        $targets = [];

        foreach ($actions as $action) {
            $action = trim($action);
            $class = classByRule($action);

            $roles = $this->roles->filter(function ($role) use ($action) {
                return $role->enabledAction($action);
            });

            foreach ($roles as $role) {
                foreach ($role->applications(true, $exclude_trashed, $class) as $app) {
                    $targets[$app->id] = $app;
                }
            }
        }

        uasort($targets, function ($a, $b) {
            return $a->name <=> $b->name;
        });

        return $targets;
    }
}
