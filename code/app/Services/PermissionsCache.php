<?php

namespace App\Services;

use App\Permission;

class PermissionsCache {
	private $cache;

	private function fetchUser($user)
	{
		$by_user = [];

		$permissions = Permission::where('user_id', '=', $user)->get();
		foreach($permissions as $perm) {
			$key = sprintf('%s/%s/%s', $perm->target_type, $perm->target_id, $perm->action);
			$by_user[$key] = true;
		}

		$this->cache[$user] = $by_user;
	}

	public function __construct()
	{
		$this->drop();
	}

	public function get($user, $action, $target_type, $target_id)
	{
		if (array_key_exists($user, $this->cache) == false)
			$this->fetchUser($user);

		$key = sprintf('%s/%s/%s', $target_type, $target_id, $action);

		if (array_key_exists($key, $this->cache[$user]))
			return 1;
		else if (array_key_exists($key, $this->cache['*']))
			return 2;
		else
			return null;
	}

	public function drop()
	{
		$this->cache = [];
		$this->fetchUser('*');
	}
}

