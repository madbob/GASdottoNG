<?php

namespace App\Services;

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

	private function checkAccessToRole($role_id)
	{
		$user = $this->ensureAuth(['gas.permissions' => 'gas', 'users.admin', 'gas']);

		$managed_roles = $user->managed_roles->search(function($item, $key) use ($role_id) {
			return $item->id == $role_id;
		});

		if ($managed_roles === false) {
			throw new AuthException(401);
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
		$this->checkAccessToRole($role_id);
		$r = Role::findOrFail($role_id);
		if ($action) {
			$r->enableAction($action);
		}
	}

	public function detachAction($role_id, $action)
	{
		$this->checkAccessToRole($role_id);
		$r = Role::findOrFail($role_id);
		if ($action) {
			$r->disableAction($action);
		}
	}
}
