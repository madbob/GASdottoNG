<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\AttachableTrait;
use App\AllowableTrait;
use App\GASModel;
use App\SluggableID;

class Gas extends Model
{
	use AttachableTrait, AllowableTrait, GASModel, SluggableID;

	public $incrementing = false;

	public function balances()
	{
		return $this->hasMany('App\Balance')->orderBy('date', 'desc');
	}

	public function alterBalance($type, $amount)
	{
		if (is_string($type))
			$type = [$type];

		$balance = $this->balances()->first();
		foreach($type as $t)
			$balance->$t += $amount;

		$balance->total += $amount;
		$balance->save();
	}

	private function mailConfig()
	{
		if ($this->mail_conf == '') {
			return (object)[
				'username' => '',
				'password' => '',
				'host' => '',
				'port' => '',
				'address' => '',
				'encryption' => ''
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

	private function ridConfig()
	{
		if ($this->rid_conf == '') {
			return (object)[
				'name' => '',
				'iban' => '',
				'code' => ''
			];
		}
		else {
			return json_decode($this->rid_conf);
		}
	}

	public function getRidnameAttribute()
	{
		return $this->ridConfig()->name;
	}

	public function getRidibanAttribute()
	{
		return $this->ridConfig()->iban;
	}

	public function getRidcodeAttribute()
	{
		return $this->ridConfig()->code;
	}

	protected function requiredAttachmentPermission()
	{
		return 'gas.config';
	}
}
