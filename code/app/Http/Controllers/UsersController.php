<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;
use Auth;
use Theme;
use Hash;

use App\UsersService;
use App\User;
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
        DB::beginTransaction();

        $user = Auth::user();
        if ($user->gas->userCan('users.admin') == false)
            return $this->errorResponse('Non autorizzato');

        $u = new User();
        $u->id = $request->input('username');
        $u->gas_id = $user->gas->id;
        $u->member_since = date('Y-m-d', time());
        $u->username = $request->input('username');
        $u->firstname = $request->input('firstname');
        $u->lastname = $request->input('lastname');
        $u->email = $request->input('email');
        $u->password = Hash::make($request->input('password'));
        $u->current_balance = 0;
        $u->previous_balance = 0;
        $u->save();

        return $this->successResponse([
            'id' => $u->id,
            'name' => $u->printableName(),
            'header' => $u->printableHeader(),
            'url' => url('users/' . $u->id)
        ]);
    }

    public function show($id)
    {
        $u = User::findOrFail($id);

        if ($u->gas->userCan('users.admin'))
            return Theme::view('user.edit', ['user' => $u]);
        else if ($u->gas->userCan('users.view'))
            return Theme::view('user.show', ['user' => $u]);
        else
            abort(503);
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        $user = Auth::user();
        if ($user->gas->userCan('users.admin') == false)
            return $this->errorResponse('Non autorizzato');

        $u = User::findOrFail($id);
        $u->username = $request->input('username');
        $u->firstname = $request->input('firstname');
        $u->lastname = $request->input('lastname');
        $u->email = $request->input('email');
        $u->phone = $request->input('phone');
        $u->birthday = $this->decodeDate($request->input('birthday'));
        $u->member_since = $this->decodeDate($request->input('member_since'));
        $u->taxcode = $request->input('taxcode');
        $u->family_members = $request->input('family_members');
        $u->card_number = $request->input('card_number');

        $password = $request->input('password');
        if ($password != '')
            $u->password = Hash::make($password);

        $u->save();

        return $this->successResponse([
            'id' => $u->id,
            'name' => $u->printableName(),
            'header' => $u->printableHeader(),
            'url' => url('users/' . $u->id)
        ]);
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        $u = User::findOrFail($id);

        if ($u->gas->userCan('users.admin') == false)
            return $this->errorResponse('Non autorizzato');

        $u->delete();
        return $this->successResponse();
    }
}
