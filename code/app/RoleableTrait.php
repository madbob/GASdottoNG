<?php

namespace App;

use Log;

trait RoleableTrait
{
    public function roles($target = null)
    {
        return $this->belongsToMany('App\Role')->orderBy('name', 'asc')->withPivot('id');
    }

    public function getManagedRolesAttribute()
    {
        return Role::sortedByHierarchy(true);
    }

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

    public function targetsByAction($action, $exclude_trashed = true)
    {
        $targets = [];
        $class = classByRule($action);

        foreach ($this->roles as $role) {
            if ($role->enabledAction($action)) {
                foreach($role->applications(true, $exclude_trashed) as $app) {
                    if ($class == null || get_class($app) == $class) {
                        $targets[$app->id] = $app;
                    }
                }
            }
        }

        return $targets;
    }
}
