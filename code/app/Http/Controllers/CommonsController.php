<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;
use Auth;
use Hash;
use Theme;

use App\Aggregate;

class CommonsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function getIndex()
    {
        $user = Auth::user();
        $user->last_login = date('Y-m-d G:i:s');
        $user->save();

        $data['notifications'] = $user->notifications;
        $data['opened'] = Aggregate::getByStatus('open');
        $data['shipping'] = Aggregate::getByStatus('closed');

        return Theme::view('pages.dashboard', $data);
    }

    public function postVerify(Request $request)
    {
        $password = $request->input('password');
        $user = $request->user();
        $test = Auth::attempt(['username' => $user->username, 'password' => $password]);
        if ($test)
            return 'ok';
        else
            return 'ko';
    }
}
