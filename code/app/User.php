<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

use DB;

use App\GASModel;
use App\SluggableID;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract
{
	use Authenticatable, CanResetPassword, GASModel, SluggableID;

	public $incrementing = false;
	protected $table = 'users';
	protected $fillable = ['firstname', 'email', 'password'];
	protected $hidden = ['password', 'remember_token'];

	public function gas()
	{
		return $this->belongsTo('App\Gas');
	}

	public function notifications()
	{
		return $this->belongsToMany('App\Notification')->withPivot('done')->where('notification_user.done', '=', false)->orderBy('start_date', 'desc');
	}

	public function allnotifications()
	{
		return $this->belongsToMany('App\Notification')->orderBy('start_date', 'desc');
	}

	public function contacts()
	{
		return $this->morphMany('App\Contact', 'target');
	}

	public function deposit()
	{
		return $this->belongsTo('App\Movement');
	}

	public function fee()
	{
		return $this->belongsTo('App\Movement');
	}

	public function getSlugID()
	{
		return str_slug($this->printableName());
	}

	public function printableName()
	{
		return $this->lastname . ' ' . $this->firstname;
	}
}
