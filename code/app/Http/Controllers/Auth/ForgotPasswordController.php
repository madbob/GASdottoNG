<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

Use Log;
use Session;

use App\User;

class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails {
        sendResetLinkEmail as realSendResetLinkEmail;
    }

    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showLinkRequestForm()
    {
        return view('auth.passwords.email');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $identifier = $request->input('username');

        $user = User::where('username', $identifier)->orWhereHas('contacts', function($query) use ($identifier) {
            $query->whereIn('type', ['email', 'skip_email'])->where('value', $identifier);
        })->first();

        if (is_null($user)) {
            Log::info('Utente non trovato per reset password: ' . $identifier);
            Session::flash('message_type', 'danger');
            Session::flash('message', _i('Username o indirizzo e-mail non trovato'));
            return redirect(url('password/reset'));
        }

        $email = $user->email;

        if (empty($email)) {
            Log::info('Utente senza email per reset password: ' . $identifier);
            Session::flash('message_type', 'danger');
            Session::flash('message', _i("L'utente indicato non ha un indirizzo mail valido"));
            return redirect(url('password/reset'));
        }

        $request->merge(['email' => $email]);
        $this->realSendResetLinkEmail($request);

        Session::flash('message', _i("Ti è stata inviata una mail col link per procedere all'aggiornamento della password"));
        return redirect(url('password/reset'));
    }

    protected function broker()
    {
        return Password::broker('bypass');
    }
}
