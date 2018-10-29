<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;

use App\Notifications\WelcomeMessage;
use App\Notifications\NewUserNotification;

use Mail;
use Hash;
use Log;

use App\User;
use App\Contact;
use App\Role;

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
        if($gas->hasFeature('public_registrations') == false)
            return redirect()->route('login');
        else
            return view('auth.register');
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
        if ($gas == null)
            return false;

        $mandatory = $gas->public_registrations['mandatory_fields'];

        $options = [
            'username' => 'required|string|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ];

        if (in_array('firstname', $mandatory))
            $options['firstname'] = 'required|string|max:255';

        if (in_array('lastname', $mandatory))
            $options['lastname'] = 'required|string|max:255';

        if (in_array('email', $mandatory))
            $options['email'] = 'required|string|email|max:255';

        if (in_array('phone', $mandatory))
            $options['phone'] = 'required|string|max:255';

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
        $user = new User();
        $user->gas_id = $data['gas_id'];
        $user->member_since = date('Y-m-d', time());
        $user->username = $data['username'];
        $user->firstname = $data['firstname'];
        $user->lastname = $data['lastname'];
        $user->password = Hash::make($data['password']);
        $user->save();

        if (!empty($data['email'])) {
            $contact = new Contact();
            $contact->target_id = $user->id;
            $contact->target_type = get_class($user);
            $contact->type = 'email';
            $contact->value = $data['email'];
            $contact->save();
        }

        if (!empty($data['phone'])) {
            $contact = new Contact();
            $contact->target_id = $user->id;
            $contact->target_type = get_class($user);
            $contact->type = 'phone';
            $contact->value = $data['phone'];
            $contact->save();
        }

        return $user;
    }

    protected function registered(Request $request, $user)
    {
        $user->notify(new WelcomeMessage());

        $admins = Role::everybodyCan('users.admin', $user->gas);
        foreach($admins as $ad) {
            try {
                $ad->notify(new NewUserNotification($user));
            }
            catch(\Exception $e) {
                Log::error('Impossibile inviare notifica registrazione nuovo utente: ' . $e->getMessage());
            }
        }

        return redirect($this->redirectTo);
    }
}
