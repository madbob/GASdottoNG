<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

use Theme;
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
        return Theme::view('auth.passwords.email');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $user = User::where('username', $request->input('username'))->first();
        if ($user == null) {
            Session::flash('message_type', 'danger');
            Session::flash('message', 'Username non trovato');
            return redirect(url('password/reset'));
        }

        $email = null;

        foreach($user->contacts as $c) {
            if ($c->type == 'email') {
                $email = $c->value;
                break;
            }
        }

        if ($email == null) {
            Session::flash('message_type', 'danger');
            Session::flash('message', "L'utente indicato non ha un indirizzo mail valido");
            return redirect(url('password/reset'));
        }

        $request->merge(['email' => $email]);
        $this->realSendResetLinkEmail($request);

        Session::flash('message', 'Ti è stata inviata una mail col link per procedere all\'aggiornamento della password');
        return redirect(url('password/reset'));
    }

    protected function broker()
    {
        return Password::broker('bypass');
    }
}
