<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use Theme;

use App\Services\UsersService;
use App\Exceptions\AuthException;
use App\Exceptions\IllegalArgumentException;

class UsersController extends Controller
{
    protected $usersService;

    public function __construct(UsersService $usersService)
    {
        $this->middleware('auth');
        $this->usersService = $usersService;

        $this->commonInit([
            'reference_class' => 'App\\User',
            'endpoint' => 'users'
        ]);
    }

    public function index()
    {
        try {
            $user = Auth::user();
            $users = $this->usersService->listUsers('', $user->can('users.admin', $user->gas));
            return Theme::view('pages.users', ['users' => $users]);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
    }

    public function search(Request $request)
    {
        $term = $request->input('term');

        try {
            $users = $this->usersService->listUsers($term);
            $users = $this->toJQueryAutocompletionFormat($users);
            return json_encode($users);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
    }

    public function profile(Request $request)
    {
        try {
            $id = Auth::user()->id;
            $user = $this->usersService->show($id);
            return Theme::view('pages.profile', ['user' => $user]);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
    }

    public function store(Request $request)
    {
        try {
            $user = $this->usersService->store($request->all());
            return $this->commonSuccessResponse($user);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
        catch (IllegalArgumentException $e) {
            return $this->errorResponse($e->getMessage(), $e->getArgument());
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $user = $this->usersService->show($id);

            if ($request->user()->can('users.admin', $user->gas))
                return Theme::view('user.edit', ['user' => $user]);
            else
                return Theme::view('user.show', ['user' => $user]);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = $this->usersService->update($id, $request->all());
            return $this->commonSuccessResponse($user);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
        catch (IllegalArgumentException $e) {
            return $this->errorResponse($e->getMessage(), $e->getArgument());
        }
    }

    public function destroy($id)
    {
        try {
            $this->usersService->destroy($id);
            return $this->successResponse();
        }
        catch (AuthException $e) {
            abort($e->status());
        }
    }

    public function picture($id)
    {
        try {
            return $this->usersService->picture($id);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
    }

    private function toJQueryAutocompletionFormat($users)
    {
        $ret = [];
        foreach ($users as $user) {
            $fullname = $user->lastname . ' ' . $user->firstname;
            $u = (object)array(
                'id' => $user->id,
                'label' => $fullname,
                'value' => $fullname
            );
            $ret[] = $u;
        }
        return $ret;
    }
}
