<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;

use Theme;

class AuthController extends Controller
{
	use AuthenticatesAndRegistersUsers, ThrottlesLogins;

	protected $username = 'username';

	public function __construct()
	{
		$this->middleware('guest', ['except' => 'getLogout']);
	}

	public function getLogin()
	{
		return Theme::view('auth.login');
	}
}
