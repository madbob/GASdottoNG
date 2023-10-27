<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

use Session;
use Auth;
use Log;

use Laravel\Socialite\Facades\Socialite;
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

    private function postLogin($request, $user)
    {
        if (Auth::check()) {
            $password = $request->input('password');
            $username = trim($request->input('username'));

            if ($username == $password) {
                Session::flash('prompt_message', _i('La password è uguale allo username! Cambiala il prima possibile dal tuo <a class="ms-2" href="%s">pannello utente</a>!', [route('profile')]));
            }
            else {
                if (is_null($user->suspended_at) == false) {
                    Session::flash('prompt_message', _i('Il tuo account è stato sospeso, e non puoi effettuare prenotazioni. Verifica lo stato dei tuoi pagamenti e del tuo credito o eventuali notifiche inviate dagli amministratori.'));
                }
            }
        }
    }

    private function failedLogin($message)
    {
        Session::flash('message', $message);
        Session::flash('message_type', 'danger');
        return redirect()->route('login');
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
            $user = User::whereHas('contacts', function($query) use ($username) {
                $query->where('type', 'email')->where('value', $username);
            })->first();

            if (is_null($user)) {
                Log::debug('Username non trovato: ' . $username);
                return $this->failedLogin(_i('Username non valido'));
            }
            else {
                $request->offsetSet('username', $user->username);
            }
        }

        LaravelGettext::setLocale($request->input('language'));

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
        $user->enforce_password_change = true;
        $user->save();

        Auth::loginUsingId($user->id);
        return redirect()->route('dashboard');
    }

    public function social($driver)
    {
        $client_id = config('service.' . $driver . '.client_id');
        $client_secret = config('service.' . $driver . '.client_secret');

        if (env('GASDOTTO_NET')) {
            /*
                Per le istanze gasdotto.net c'è un unico URL valido per i
                redirect OAuth, da cui poi si viene smistati sulla rotta
                login.social.back dell'istanza giusta
            */
            $redirect_url = sprintf('https://gasdotto.net/social/%s?instance=%s', $driver, current_instance());
        }
        else {
            $redirect_url = route('login.social.back', $driver);
        }

        $config = new \SocialiteProviders\Manager\Config($client_id, $client_secret, $redirect_url);
        return Socialite::driver($driver)->setConfig($config)->redirect();
    }

    private function retrieveSocialUser($user, $driver)
    {
        $identifier = $user->id;
        $driver_contact_type = sprintf('%s_id', $driver);

        $u = User::whereHas('contacts', function($query) use ($identifier, $driver_contact_type) {
            $query->where('type', $driver_contact_type)->where('value', $identifier);
        })->first();

        if (is_null($u)) {
            $email = $user->email;
            if (filled($email)) {
                $u = User::whereHas('contacts', function($query) use ($email) {
                    $query->whereIn('type', ['email', 'skip_email'])->where('value', $email);
                })->first();
            }
        }

        return $u;
    }

    public function socialCallback(Request $request, $driver)
    {
        $user = Socialite::driver($driver)->user();

        if ($user) {
            $u = $this->retrieveSocialUser($user, $driver);
            if ($u) {
                Auth::loginUsingId($u->id);
                return redirect()->route('dashboard');
            }

            $gas = currentAbsoluteGas();

            if ($gas->hasFeature('public_registrations')) {
                $social_identifier = sprintf('%s_id::%s', $driver, $user->id);
                Session::put('from_social', $social_identifier);
                Session::put('from_social_email', $user->email);
                return redirect()->route('register');
            }
            else {
                return $this->failedLogin(_i('Utente non riconosciuto. Per accedere è necessario farsi prima creare un account da un amministratore.'));
            }
        }
        else {
            return $this->failedLogin(_i('Errore nella fase di autenticazione'));
        }
    }
}
