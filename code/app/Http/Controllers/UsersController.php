<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use Theme;

use App\Services\UsersService;
use App\Exceptions\AuthException;
use App\Exceptions\IllegalArgumentException;

class UsersController extends BackedController
{
    public function __construct(UsersService $service)
    {
        $this->middleware('auth');

        $this->commonInit([
            'reference_class' => 'App\\User',
            'endpoint' => 'users',
            'service' => $service
        ]);
    }

    public function index()
    {
        try {
            $user = Auth::user();
            $users = $this->service->listUsers('', $user->can('users.admin', $user->gas));
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
            $users = $this->service->listUsers($term);
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
            $user = $this->service->show($id);
            return Theme::view('pages.profile', ['user' => $user]);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $user = $this->service->show($id);

            if ($request->user()->can('users.admin', $user->gas))
                return Theme::view('user.edit', ['user' => $user]);
            else
                return Theme::view('user.show', ['user' => $user]);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
    }

    public function picture($id)
    {
        try {
            return $this->service->picture($id);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
    }

    private function toJQueryAutocompletionFormat($users)
    {
        $ret = [];
        foreach ($users as $user) {
            $fullname = $user->printableName();
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
