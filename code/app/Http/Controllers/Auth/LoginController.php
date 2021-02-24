<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

use Session;
use Auth;
use Log;

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
        $gas = currentAbsoluteGas();
        return view('auth.login', ['gas' => $gas]);
    }

    public function login(Request $request)
    {
        $username = $request->input('username');

        $user = User::where('username', $username)->first();

        if (is_null($user)) {
            Session::flash('message', _i('Username non valido'));
            Session::flash('message_type', 'danger');
            Log::debug('Username non trovato: ' . $username);
            return redirect(url('login'));
        }

        if ($user->gas->restricted == '1' && $user->can('gas.access', $user->gas) == false) {
            return redirect(url('login'));
        }

        LaravelGettext::setLocale($request->input('language'));

        $ret = $this->realLogin($request);

        if (Auth::check()) {
            $password = $request->input('password');
            if ($username == $password) {
                Session::flash('prompt_message', _i('La password è uguale allo username! Cambiala il prima possibile dal tuo <a href="%s">pannello utente</a>!', [route('profile')]));
            }
            else {
                $user = User::where('username', $username)->first();
                if (!is_null($user->suspended_at)) {
                    Session::flash('prompt_message', _i('Il tuo account è stato sospeso, e non puoi effettuare prenotazioni. Verifica lo stato dei tuoi pagamenti e del tuo credito o eventuali notifiche inviate dagli amministratori.'));
                }
            }
        }

        return $ret;
    }
}
