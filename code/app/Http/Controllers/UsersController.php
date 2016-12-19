<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use Auth;
use Theme;
use Hash;

use App\UsersService;
use App\Exceptions\AuthException;

class UsersController extends Controller
{

    protected $usersService;

    public function __construct(UsersService $usersService)
    {
        $this->middleware('auth');
        $this->usersService = $usersService;
    }

    public function index()
    {
        try {
            $users = $this->usersService->listUsers();
            return Theme::view('pages.users', ['users' => $users]);
        } catch (AuthException $e) {
            abort($e->status());
        }
    }

    public function search(Request $request)
    {
        $term = $request->input('term');

        try {
            $users = $this->usersService->search($term);

            return json_encode($users);
        } catch (AuthException $e) {
            abort($e->status());
        }
    }

    public function store(Request $request)
    {
        try {
            $user = $this->usersService->store($request);

            return $this->userSuccessResponse($user);
        } catch (AuthException $e) {
            abort($e->status());
        }
    }

    public function show($id)
    {
        try {
            $user = $this->usersService->show($id);

            if ($user->gas->userCan('users.admin')) {
                return Theme::view('user.edit', ['user' => $user]);
            }

            return Theme::view('user.show', ['user' => $user]);
        } catch (AuthException $e) {
            abort($e->status());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = $this->usersService->update($request, $id);

            return $this->userSuccessResponse($user);
        } catch (AuthException $e) {
            abort($e->status());
        }
    }

    public function destroy($id)
    {
        try {
            $this->usersService->destroy($id);

            return $this->successResponse();
        } catch (AuthException $e) {
            abort($e->status());
        }
    }

    private function userSuccessResponse($user)
    {
        return $this->successResponse([
            'id' => $user->id,
            'name' => $user->printableName(),
            'header' => $user->printableHeader(),
            'url' => url('users/' . $user->id)
        ]);
    }
}
