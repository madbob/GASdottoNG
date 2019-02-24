<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

use Session;
use Auth;

use LaravelGettext;

use App\User;
use App\Gas;

class LoginController extends Controller
{
    use AuthenticatesUsers {
        login as realLogin;
    }

    protected $redirectTo = '/dashboard';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function username()
    {
        return 'username';
    }

    public function showLoginForm()
    {
        $gas = Gas::first();
        return view('auth.login', ['gas' => $gas]);
    }

    public function login(Request $request)
    {
        $username = $request->input('username');

        $gas = Gas::first();
        if ($gas->restricted == '1') {
            $user = User::where('username', $username)->first();
            if (is_null($user) || $user->can('gas.access', $gas) == false) {
                return redirect(url('login'));
            }
        }

        LaravelGettext::setLocale($request->input('language'));

        $ret = $this->realLogin($request);

        if (Auth::check()) {
            $password = $request->input('password');
            if ($username == $password)
                Session::flash('prompt_message', _i('La password è uguale allo username! Cambiala il prima possibile dal tuo <a href="%s">pannello utente</a>!', [route('profile')]));
        }

        return $ret;
    }
}
