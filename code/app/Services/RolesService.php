<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

use App\Exceptions\AuthException;

use App\Role;
use App\User;

class RolesService extends BaseService
{
	public function store(array $request)
	{
		$this->ensureAuth(['gas.permissions' => 'gas']);

		$role = new Role();
		$this->setIfSet($role, $request, 'name');
		$this->setIfSet($role, $request, 'parent_id');
		$role->actions = join(',', $request['actions'] ?? []);
		$role->save();

		return $role;
	}

	public function update($id, array $request)
	{
		$this->ensureAuth(['gas.permissions' => 'gas']);

		$role = Role::findOrFail($id);
		$this->setIfSet($role, $request, 'name');
		$this->setIfSet($role, $request, 'parent_id');
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
		Nota bene: le funzioni per assegnare o revocare un ruolo devono
		funzionare a prescindere dal permesso gas.permissions, almeno sui ruoli
		che gerarchicamente sono "inferiori" a quelli dell'utente corrente
	*/
	private function checkAccessToRole($role_id)
	{
		$user = Auth::user();

		$managed_roles = $user->managed_roles->search(function($item, $key) use ($role_id) {
			return $item->id == $role_id;
		});

		if ($managed_roles === false) {
			/*
				Se il ruolo desiderato non è tra quelli gestibili
				gerarchicamente, occorre avere il permesso globale per alterare
				tutti i permessi
			*/
			$this->ensureAuth(['gas.permissions' => 'gas']);
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
			foreach($r->getAllClasses() as $target_class) {
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

            foreach($r->users as $user) {
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
		$new_actions = array_filter($actions, fn($a) => $a != $action);
        $r->actions = join(',', $new_actions);
        $r->save();
	}
}
