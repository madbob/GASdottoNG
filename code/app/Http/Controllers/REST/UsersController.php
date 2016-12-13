<?php

namespace App\Http\Controllers\REST;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use Auth;
use Response;

use App\UsersService;
use App\Exceptions\AuthException;
use App\Exceptions\IllegalArgumentException;

class UsersController extends Controller
{

    protected $usersService;

    public function __construct(UsersService $usersService)
    {
        $this->middleware('nodebugbar');
        $this->usersService = $usersService;
    }

    public function index()
    {
        try {
            $users = $this->usersService->listUsers();
            return response()->json(['users' => $users], 200);
        } catch (AuthException $e) {
            return response(null, $e->status());
        }
    }

    public function search(Request $request)
    {
        $term = trim($request->input('term'));

        if (empty($term)) {
            throw new IllegalArgumentException("Search term is empty");
        }

        try {
            $users = $this->usersService->search($term);

            return response()->json(['users' => $users], 200);
        } catch (AuthException $e) {
            return response(null, $e->status());
        }
    }

}
