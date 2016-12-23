<?php

namespace app\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Theme;
use App\User;
use App\Gas;

class AuthController extends Controller
{
    use AuthenticatesAndRegistersUsers {
        postLogin as realPostLogin;
    }

    protected $username = 'username';

    public function __construct()
    {
        $this->middleware('guest', ['except' => 'getLogout']);
    }

    public function getLogin()
    {
        $gas = Gas::first();

        return Theme::view('auth.login', ['gas' => $gas]);
    }

    public function postLogin(Request $request)
    {
        $gas = Gas::first();
        if ($gas->restricted == '1') {
            $username = $request->input('username');
            $user = User::where('username', $username)->first();
            if ($user == null || $gas->userCan('gas.super', $user) == false) {
                return redirect(url('auth/login'));
            }
        }

        return $this->realPostLogin($request);
    }
}
