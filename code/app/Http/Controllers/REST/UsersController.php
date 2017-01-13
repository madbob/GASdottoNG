<?php

namespace App\Http\Controllers\REST;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Response;
use App\Services\UsersService;
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
            throw new IllegalArgumentException('Search term is empty');
        }

        try {
            $users = $this->usersService->listUsers($term);

            return response()->json(['users' => $users], 200);
        } catch (AuthException $e) {
            return response(null, $e->status());
        }
    }

    public function show($id)
    {
        try {
            $user = $this->usersService->show($id);

            return response()->json(['user' => $user], 200);
        } catch (AuthException $e) {
            return response(null, $e->status());
        }
    }

    public function destroy($id)
    {
        try {
            $this->usersService->destroy($id);

            return response(null, 200);
        } catch (AuthException $e) {
            return response(null, $e->status());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = $this->usersService->update($id, $request->all());

            return response()->json(['user' => $user], 200);
        } catch (AuthException $e) {
            return response(null, $e->status());
        }
    }

    public function store(Request $request)
    {
        try {
            $user = $this->usersService->store($request->all());

            return response()->json(['user' => $user], 200);
        } catch (AuthException $e) {
            return response(null, $e->status());
        }
    }
}
