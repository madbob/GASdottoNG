<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\GASModel;

class Notification extends Model
{
	use GASModel;

	public function users()
	{
		return $this->belongsToMany('App\User');
	}

	public function hasUser($user)
	{
		foreach ($this->users as $u)
			if ($u->id == $user->id)
				return true;

		return false;
	}

	public function printableName()
	{
		$users = $this->users;
		$c = $users->count();

		if ($c == 1)
			return $users->first()->printableName();
		else
			return sprintf('%d utenti', $c);
	}

	public function printableHeader()
	{
		return $this->printableDate('start_date') . ' / ' . $this->printableName();
	}
}
