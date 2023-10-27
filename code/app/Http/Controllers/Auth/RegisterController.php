<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;

use App\Rules\Captcha;
use App\Rules\EMail;
use App\Notifications\WelcomeMessage;
use App\Notifications\NewUserNotification;

use App\Gas;
use App\User;
use App\Contact;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showRegistrationForm()
    {
        $gas = currentAbsoluteGas();

        if($gas->hasFeature('public_registrations') == false) {
            return redirect()->route('login');
        }
        else {
            $social = Session::get('from_social');
            $email = Session::get('from_social_email');

            if ($social) {
                return view('auth.register', [
                    'social' => $social,
                    'email' => $email,
                ]);
            }
            else {
                $first = rand(1, 20);
                $second = rand(1, 20);
                $captcha = sprintf('%s + %s =', $first, $second);
                Session::put('captcha_solution', $first + $second);
                return view('auth.register', ['captcha' => $captcha]);
            }
        }
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $gas = Gas::find($data['gas_id']);
        if ($gas == null) {
            throw new \Exception('No GAS selected', 1);
        }

        $social = $data['social'] ?? null;

        if (is_null($social)) {
            $options = [
                'username' => 'required|string|max:255|unique:users',
                'password' => 'required|string|min:6|confirmed',
                'verify' => [new Captcha()]
            ];
        }
        else {
            $options = [];
        }

        $mandatory = $gas->public_registrations['mandatory_fields'];

        if (in_array('firstname', $mandatory)) {
            $options['firstname'] = 'required|string|max:255';
        }

        if (in_array('lastname', $mandatory)) {
            $options['lastname'] = 'required|string|max:255';
        }

        if (in_array('email', $mandatory)) {
            $options['email'] = ['required', 'string', 'email', 'max:255', new EMail()];
        }

        if (in_array('phone', $mandatory)) {
            $options['phone'] = 'required|string|max:255';
        }

        return Validator::make($data, $options);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        $gas = Gas::find($data['gas_id']);

        $user = new User();
        $user->gas_id = $data['gas_id'];
        $user->member_since = date('Y-m-d', time());
        $user->firstname = $data['firstname'];
        $user->lastname = $data['lastname'];

        $username = $data['username'] ?? ($data['email'] ?? Str::random(30));
        $user->username = $username;

        $password = $data['password'] ?? Str::random(30);
        $user->password = Hash::make($password);

        $manual = $gas->public_registrations['manual'];
        if ($manual) {
            $user->pending = true;
        }

        $user->save();

        if (isset($data['email']) && empty($data['email']) == false) {
            $user->addContact('email', $data['email']);
        }

        if (isset($data['phone']) && empty($data['phone']) == false) {
            $user->addContact('phone', $data['phone']);
        }

        if (isset($data['social']) && empty($data['social']) == false) {
            list($identifier, $value) = explode('::', $data['social']);
            $user->addContact($identifier, $value);
        }

        return $user;
    }

    protected function registered(Request $request, $user)
    {
        Session::forget('captcha_solution');
        Session::forget('from_social');
        Session::forget('from_social_email');

        try {
            $user->notify(new WelcomeMessage());
        }
        catch(\Exception $e) {
            \Log::error('Impossibile inviare mail di verifica a nuovo utente: ' . $e->getMessage());
        }

        $admins = everybodyCan('users.admin', $user->gas);
        foreach($admins as $ad) {
            try {
                $ad->notify(new NewUserNotification($user));
            }
            catch(\Exception $e) {
                \Log::error('Impossibile inviare notifica registrazione nuovo utente: ' . $e->getMessage());
            }
        }

        return redirect($this->redirectTo);
    }
}
