<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

use App\GASModel;

class User extends Model implements AuthenticatableContract,
				    CanResetPasswordContract
{
	use Authenticatable, CanResetPassword, GASModel;

	protected $table = 'users';
	protected $fillable = ['name', 'email', 'password'];
	protected $hidden = ['password', 'remember_token'];

	public function gas()
	{
		return $this->belongsTo('App\Gas');
	}

	public function notifications()
	{
		return $this->belongsToMany('App\Notification');
	}

	public function contacts()
	{
		return $this->morphMany('App\Contact', 'target');
	}

	public function movements()
	{
		return $this->hasMany('App\Movement')->orderBy('created_at', 'desc');
	}

	public function deposit()
	{
		return $this->movements->where('type', '=', 'deposit_payment')->where('target', '=', $this->gas)->first();
	}

	public function fee()
	{
		return $this->movements->where('type', '=', 'annual_payment')->where('target', '=', $this->gas)->first();
	}

	public function printableName()
	{
		return $this->surname . ' ' . $this->name;
	}
}
