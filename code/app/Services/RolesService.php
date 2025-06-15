<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

use MadBob\Larastrap\Integrations\LarastrapStack;

use App\Role;
use App\User;

class RolesService extends BaseService
{
    public function initSystemRoles()
    {
        $queue = systemParameters('Roles');

        while (true) {
            $next_queue = [];

            foreach ($queue as $identifier => $instance) {
                if (Role::where('identifier', $identifier)->count() == 0) {
                    try {
                        $instance->create();
                    }
                    catch (\Exception $e) {
                        $next_queue[$identifier] = $instance;
                    }
                }
            }

            if (empty($next_queue)) {
                break;
            }

            $queue = $next_queue;
        }
    }

    public function store(array $request)
    {
        $this->ensureAuth(['gas.permissions' => 'gas']);
        $role = LarastrapStack::autoreadSave($request, Role::class);
        $role->save();

        return $role;
    }

    public function destroy($id)
    {
        $this->ensureAuth(['gas.permissions' => 'gas']);

        $role = Role::findOrFail($id);
        $role->delete();

        return $role;
    }

    /*
        Funzione che istruisce il sistema interno di controllo autorizzazioni a
        gestire i permessi personalizzati. Nella stragrande maggioranza dei casi
        è sufficiente invocare questa funzione in AuthServiceProvider, ma viene
        comunque messa qui affinché possa essere nuovamente invocata in casi
        particolari (e.g. viene abilitata la modalità Multi-GAS, che prevede
        l'esistenza di un permesso nuovo da applicare)
    */
    public function registerPolicies()
    {
        $all_permissions = allPermissions();

        foreach ($all_permissions as $rules) {
            foreach (array_keys($rules) as $identifier) {
                if (Gate::has($identifier)) {
                    continue;
                }

                Gate::define($identifier, function ($user, $obj = null) use ($identifier) {
                    foreach ($user->roles as $role) {
                        if ($role->enabledAction($identifier)) {
                            if (is_null($obj) || $role->applies($obj)) {
                                return true;
                            }
                        }
                    }

                    return false;
                });
            }
        }
    }

    public function setMasterRole($gas, $identifier, $role_id)
    {
        $this->ensureAuth(['gas.permissions' => 'gas']);

        $conf = $gas->roles;

        if ($identifier == 'friend') {
            $old_friend_role = $conf['friend'];
        }
        else {
            $old_friend_role = null;
        }

        $conf[$identifier] = $role_id;
        $gas->setConfig('roles', $conf);

        /*
            Se il ruolo "amico" viene cambiato, cambio effettivamente gli utenti
            coinvolti
        */
        if ($old_friend_role) {
            $friends = User::whereNotNull('parent_id')->get();

            foreach ($friends as $friend) {
                $friend->removeRole($old_friend_role, $gas);
                $friend->addRole($conf['friend'], $gas);
            }
        }
    }

    /*
        Nota bene: le funzioni per assegnare o revocare un ruolo devono
        funzionare a prescindere dal permesso gas.permissions, almeno sui ruoli
        che gerarchicamente sono "inferiori" a quelli dell'utente corrente
    */
    private function checkAccessToRole($role_id)
    {
        $user = Auth::user();

        $managed_roles = $user->managed_roles->search(function ($item, $key) use ($role_id) {
            return $item->id == $role_id;
        });

        if ($managed_roles === false) {
            /*
                Se il ruolo desiderato è uno di quelli base "utente" e "amico"
                basta il permesso di gestione degli utenti, in quanto
                l'amministratore degli utenti deve poter intervenire (in
                particolare sugli amici)
            */
            if ((isset($user->gas->roles['friend']) && $role_id == $user->gas->roles['friend']) || (isset($user->gas->roles['user']) && $role_id == $user->gas->roles['user'])) {
                $this->ensureAuth(['users.admin' => 'gas']);
            }
            else {
                $this->ensureAuth(['gas.permissions' => 'gas']);
            }
        }
    }

    public function attachUser($user_id, $role_id, $target)
    {
        $this->checkAccessToRole($role_id);
        $r = Role::findOrFail($role_id);
        $u = User::tFind($user_id, true);

        $attached = $u->addRole($r, $target);

        if (is_null($target)) {
            /*
                Se il nuovo ruolo prevede l'associazione ad un modello di cui
                esiste una sola istanza, avviene automaticamente l'assegnazione.
                Questo serve in particolare ad assegnare i ruoli nel contesto di
                un GAS, quando ce ne è uno solo (ovvero: la maggior parte dei
                casi), ed evitare confusione da parte degli utenti
            */
            foreach ($r->getAllClasses() as $target_class) {
                $available_targets = $target_class::tAll();
                if ($available_targets->count() == 1) {
                    $attached->attachApplication($available_targets->get(0));
                }
            }
        }

        return [$u, $r];
    }

    public function detachUser($user_id, $role_id, $target)
    {
        $this->checkAccessToRole($role_id);
        $r = Role::findOrFail($role_id);
        $u = User::tFind($user_id, true);
        $u->removeRole($r, $target);

        return [$u, $r];
    }

    public function attachAction($role_id, $action)
    {
        $this->ensureAuth(['gas.permissions' => 'gas']);

        $r = Role::findOrFail($role_id);
        if ($r->enabledAction($action)) {
            return;
        }

        $r->actions .= ',' . $action;
        $r->save();

        /*
            Se attivo un permesso che ha un solo target (di solito: il GAS),
            attacco quest'ultimo direttamente a tutti gli utenti coinvolti
        */
        $class = classByRule($action);
        if ($class::count() == 1) {
            $only_target = $class::first();

            foreach ($r->users as $user) {
                $urole = $user->roles()->where('roles.id', $r->id)->first();
                $urole->attachApplication($only_target);
            }
        }
    }

    public function detachAction($role_id, $action)
    {
        $this->ensureAuth(['gas.permissions' => 'gas']);
        $r = Role::findOrFail($role_id);
        $actions = explode(',', $r->actions);
        $new_actions = array_filter($actions, fn ($a) => $a != $action);
        $r->actions = implode(',', $new_actions);
        $r->save();
    }

    public function export()
    {
        $filename = sprintf('%s.csv', __('texts.permissions.name'));

        $headers = [''];
        $data = [];

        $users = User::topLevel()->with('roles')->get();
        foreach ($users as $index => $user) {
            $data[] = [$user->printableName()];
        }

        $roles = Role::has('users')->orderBy('name')->get();
        foreach ($roles as $role) {
            $headers[] = $role->printableName();

            foreach ($users as $index => $user) {
                $cell = '';

                $attached = $user->roles->firstWhere('id', $role->id);
                if ($attached) {
                    $app_string = [];
                    $applications = $attached->applications(true, true);
                    foreach ($applications as $app) {
                        $app_string[] = $app->printableName();
                    }

                    $cell = implode("\n", $app_string);
                }

                $data[$index][] = $cell;
            }
        }

        return output_csv($filename, $headers, $data, null);
    }
}
