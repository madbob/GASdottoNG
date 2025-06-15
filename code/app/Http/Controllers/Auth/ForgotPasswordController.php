<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

use Log;
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

        $user = User::where('username', $identifier)->orWhereHas('contacts', function ($query) use ($identifier) {
            $query->where('type', 'email')->where('value', $identifier);
        })->first();

        if (is_null($user)) {
            Log::info('Utente non trovato per reset password: ' . $identifier);
            Session::flash('message_type', 'danger');
            Session::flash('message', __('texts.auth.help.missing_user_or_mail'));

            return redirect(url('password/reset'));
        }

        $email = $user->email;

        if (empty($email)) {
            Log::info('Utente senza email per reset password: ' . $identifier);
            Session::flash('message_type', 'danger');
            Session::flash('message', __('texts.auth.help.missing_email'));

            return redirect(url('password/reset'));
        }

        $request->merge(['email' => $email]);
        $this->realSendResetLinkEmail($request);

        Session::flash('message', __('texts.auth.help.reset_email_notice'));

        return redirect(url('password/reset'));
    }

    protected function broker()
    {
        return Password::broker('bypass');
    }
}
