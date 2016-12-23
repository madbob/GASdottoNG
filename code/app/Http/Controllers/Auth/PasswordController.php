<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Mail\Message;
use DB;
use Theme;
use App\User;

class PasswordController extends Controller
{
    use ResetsPasswords;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('guest');
    }

    public function getEmail()
    {
        return Theme::view('auth.password');
    }

    public function postEmail(Request $request)
    {
        $this->validate($request, ['username' => 'required']);

        $user = User::where(DB::raw('LOWER(username)'), '=', strtolower($request->input('username')))->first();
        if ($user == null) {
            return redirect()->back()->withErrors(['username' => 'Username non riconosciuto']);
        }

        $response = Password::sendResetLink(['email' => $user->email], function (Message $message) {
            $message->subject($this->getEmailSubject());
        });

        switch ($response) {
            case Password::RESET_LINK_SENT:
                return redirect()->back()->with('status', trans($response));

            case Password::INVALID_USER:
                return redirect()->back()->withErrors(['email' => trans($response)]);
        }
    }

    public function getReset($token)
    {
        return Theme::view('auth.reset', ['token' => $token]);
    }

    public function postReset(Request $request)
    {
        $this->validate($request, [
            'token' => 'required',
            'username' => 'required',
            'password' => 'required|confirmed',
        ]);

        $credentials = $request->only(
            'username', 'password', 'password_confirmation', 'token'
        );

        $response = Password::reset($credentials, function ($user, $password) {
            $this->resetPassword($user, $password);
        });

        switch ($response) {
            case Password::PASSWORD_RESET:
                return redirect($this->redirectPath());

            default:
                return redirect()->back()->withInput($request->only('username'))->withErrors(['username' => trans($response)]);
        }
    }
}
