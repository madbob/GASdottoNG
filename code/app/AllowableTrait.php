<?php

namespace App;

use Auth;

use App\Permission;

trait AllowableTrait
{
	public function permissions()
	{
		return $this->morphMany('App\Permission', 'target');
	}

	private function permissionType($perm)
	{
		if ($perm == null)
			return 0;
		else if ($perm->user_id == 0)
			return 2;
		else
			return 1;
	}

	/*
		Restituisce
		0 se l'utente non e' autorizzato
		1 se l'utente e' autorizzato
		2 se tutti gli utenti (e dunque anche quello richiesto) sono autorizzati

		L'utente viene considerato autorizzato se una qualsiasi delle
		azioni richieste è concessa
	*/
	public function userCan($action, $user = null)
	{
		if ($user == null)
			$user = Auth::user();

		$user_id = $user->id;
		$perm = null;

		$actions = explode('|', $action);
		foreach ($actions as $a) {
			$perm = $this->permissions()->where('action', '=', $a)->where(function($query) use ($user_id) {
				$query->where('user_id', '=', $user_id)->orWhere('user_id', '=', 0);
			})->first();

			if ($perm != null)
				break;
		}

		return $this->permissionType($perm);
	}

	/*
		Verifica che l'utente abbia almeno una autorizzazione del tipo
		richiesto, senza specificare un target specifico.
		Può essere invocata su qualsiasi oggetto che usa AllowableTrait
	*/
	public function userHas($action, $user = null)
	{
		if ($user == null)
			$user = Auth::user();

		$user_id = $user->id;
		$perm = null;

		$actions = explode('|', $action);
		foreach ($actions as $a) {
			$perm = Permission::where('action', '=', $a)->where(function($query) use ($user_id) {
				$query->where('user_id', '=', $user_id)->orWhere('user_id', '=', 0);
			})->first();

			if ($perm != null)
				break;
		}

		return $this->permissionType($perm);
	}

	/*
		Verifica che almeno un utente abbia il permesso richiesto / uno
		dei permessi richiesti sull'oggetto corrente
	*/
	public function oneCan($action)
	{
		$perm = null;

		$actions = explode('|', $action);
		foreach ($actions as $a) {
			$perm = $this->permissions()->where('action', '=', $a)->where('target_id', '=', $this->id)->first();
			if ($perm != null)
				break;
		}

		return $this->permissionType($perm);
	}

	public function userPermit($action, $user)
	{
		if ($user != null) {
			if (is_object($user))
				$id = $user->id;
			else if (is_string($user))
				$id = $user;
			else
				return;
		}
		else {
			$user = Auth::user();
			$id = $user->id;
		}

		$actions = explode('|', $action);
		foreach ($actions as $a)
			$t = $this->permissions()->firstOrCreate(['action' => $a, 'user_id' => $id]);
	}

	public function userRevoke($action, $user)
	{
		if ($user != null) {
			if (is_object($user))
				$id = $user->id;
			else if (is_string($user))
				$id = $user;
			else
				return;
		}
		else {
			$user = Auth::user();
			$id = $user->id;
		}

		$actions = explode('|', $action);
		foreach ($actions as $a)
			$this->permissions()->where('action', '=', $a)->where('user_id', '=', $id)->delete();
	}

	public function deletePermissions()
	{
		$this->permissions()->delete();
	}
}
