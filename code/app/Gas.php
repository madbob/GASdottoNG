<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use TeamTeaTime\Filer\AttachableTrait;

use App\AllowableTrait;
use App\HasBalance;
use App\GASModel;
use App\SluggableID;

class Gas extends Model
{
	use AttachableTrait, AllowableTrait, HasBalance, GASModel, SluggableID;

	public $incrementing = false;

	private function mailConfig()
	{
		if ($this->mail_conf == '') {
			return (object)[
				'username' => '',
				'password' => '',
				'host' => '',
				'port' => '',
				'address' => '',
				'encryption' => '',
			];
		}
		else {
			return json_decode($this->mail_conf);
		}
	}

	public function getMailusernameAttribute()
	{
		return $this->mailConfig()->username;
	}

	public function getMailpasswordAttribute()
	{
		return $this->mailConfig()->password;
	}

	public function getMailserverAttribute()
	{
		return $this->mailConfig()->host;
	}

	public function getMailportAttribute()
	{
		return $this->mailConfig()->port;
	}

	public function getMailaddressAttribute()
	{
		return $this->mailConfig()->address;
	}

	public function getMailsslAttribute()
	{
		return ($this->mailConfig()->encryption != '');
	}
}
