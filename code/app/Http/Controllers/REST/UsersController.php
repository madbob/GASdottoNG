<?php

namespace App\Http\Controllers\REST;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Auth;
use Response;

use App\UsersService;
use App\Exceptions\AuthException;

class UsersController extends Controller
{
	
	protected $usersService;
	
	public function __construct(UsersService $usersService)
	{
		$this->usersService = $usersService;
	}

	public function index()
	{
		try {
			$users = $this->usersService->list();
			return response()->json(['users' => $users], 200);
		} catch (AuthException $e) {
			return response()->json(null, $e->status());
		}
	}

}
