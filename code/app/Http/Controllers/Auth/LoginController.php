<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

use App\Http\Controllers\Controller;
use App\User;

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
        $gas = currentAbsoluteGas();

        return view('auth.login', ['gas' => $gas]);
    }

    private function postLogin($request, $user)
    {
        if (Auth::check()) {
            $password = $request->input('password');
            $username = trim($request->input('username'));

            if ($username == $password) {
                Session::flash('prompt_message', __('texts.auth.help.username_same_password', ['link' => route('profile')]));
            }
            else {
                if (is_null($user->suspended_at) === false) {
                    Session::flash('prompt_message', __('texts.auth.help.suspended_account_notice'));
                }
            }
        }
    }

    public function login(Request $request)
    {
        $username = trim($request->input('username'));

        $user = User::where('username', $username)->first();
        if (is_null($user)) {
            /*
                Molti utenti si confondono, tentando di usare l'indirizzo email
                al posto dello username. Qui tento di recuperare l'utente anche
                in base all'indirizzo email, premesso che è un metodo fallace
                (diversi utenti possono avere uno stesso indirizzo)
            */
            $user = User::whereHas('contacts', function ($query) use ($username) {
                $query->where('type', 'email')->where('value', $username);
            })->first();

            if (is_null($user)) {
                Session::flash('message', __('texts.auth.help.invalid_username'));
                Session::flash('message_type', 'danger');
                Log::debug('Username non trovato: ' . $username);

                return redirect(url('login'));
            }
            else {
                $request->offsetSet('username', $user->username);
            }
        }

        App::setLocale($request->input('language'));

        $ret = $this->realLogin($request);
        $this->postLogin($request, $user);

        return $ret;
    }

    public function autologin(Request $request, $token)
    {
        $user = User::where('access_token', $token)->first();
        if (is_null($user)) {
            abort(503);
        }

        $user->access_token = '';
        $user->save();

        Auth::loginUsingId($user->id);

        return redirect()->route('dashboard');
    }
}
