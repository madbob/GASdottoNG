<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

use App\Gas;
use App\User;
use App\Role;

class MultiGasService extends BaseService
{
    public function list()
    {
        $user = $this->ensureAuth(['gas.multi' => 'gas']);
        $groups = [];

        $roles = $user->roles->filter(function ($role) {
            return $role->enabledAction('gas.multi');
        });

        foreach ($roles as $role) {
            foreach ($role->applications(false, false, Gas::class) as $obj) {
                $groups[] = $obj;
            }
        }

        return array_unique($groups, SORT_REGULAR);
    }

    public function show($id)
    {
        $gas = Gas::findOrFail($id);
        $this->ensureAuth(['gas.multi' => $gas]);

        return $gas;
    }

    private function attachAdmin($user, $admin, $gas)
    {
        $roles = [];

        $target_role = $user->gas->roles['multigas'] ?? -1;
        if ($target_role != -1) {
            $role = Role::find($target_role);
            if ($role) {
                $roles = [$role];
            }
        }

        if (empty($roles)) {
            $roles = Role::havingAction('gas.permissions');
        }

        foreach ($roles as $role) {
            $admin->addRole($role, $gas);
        }
    }

    public function store(array $request)
    {
        $user = $this->ensureAuth(['gas.multi' => 'gas']);

        DB::beginTransaction();

        $user_params = array_intersect_key($request, array_flip(['username', 'firstname', 'lastname', 'password', 'enforce_password_change']));
        $admin = app()->make('UsersService')->store($user_params);

        $gas = new Gas();
        $this->setIfSet($gas, $request, 'name');
        $gas->save();

        /*
            Copio le configurazioni del GAS attuale su quello nuovo
        */
        foreach ($user->gas->configs as $config) {
            $new = $config->replicate();
            $new->gas_id = $gas->id;
            $new->save();
        }

        /*
            Aggancio il nuovo GAS all'utente corrente, affinché lo possa
            manipolare
        */
        $roles = Role::havingAction('gas.multi');
        foreach ($roles as $role) {
            $user->addRole($role, $gas);
        }

        /*
            Aggancio il nuovo utente amministratore al nuovo GAS (di default
            verrebbe assegnato al GAS corrente)
        */
        $admin->gas_id = $gas->id;
        $admin->save();

        $this->attachAdmin($user, $admin, $gas);

        DB::commit();

        return $gas;
    }

    public function update($id, array $request)
    {
        $gas = $this->show($id);
        $this->setIfSet($gas, $request, 'name');
        $gas->save();

        return $gas;
    }

    public function destroy($id)
    {
        $gas = $this->show($id);

        /*
            Il modello User ha uno scope globale per manipolare solo gli utenti
            del GAS locale, ma qui serve fare esattamente il contrario (ovvero:
            manipolare solo gli utenti del GAS selezionato)
        */
        foreach ($gas->users()->withoutGlobalScopes()->withTrashed()->get() as $u) {
            $u->forceDelete();
        }

        foreach ($gas->configs as $c) {
            $c->delete();
        }

        $gas->suppliers()->sync([]);
        $gas->aggregates()->sync([]);
        $gas->deliveries()->sync([]);

        $gas->delete();

        return $gas;
    }

    private function operate($request, $function)
    {
        DB::beginTransaction();

        $gas_id = $request['gas'];
        $gas = $this->show($gas_id);

        $target_id = $request['target_id'];
        $target_type = $request['target_type'];

        switch ($target_type) {
            case 'supplier':
                $gas->suppliers()->$function($target_id);
                break;

            case 'aggregate':
                $gas->aggregates()->$function($target_id);
                break;

            case 'delivery':
                $gas->deliveries()->$function($target_id);
                break;
        }

        DB::commit();
    }

    public function attach($request)
    {
        $this->operate($request, 'attach');
    }

    public function detach($request)
    {
        $this->operate($request, 'detach');
    }
}
