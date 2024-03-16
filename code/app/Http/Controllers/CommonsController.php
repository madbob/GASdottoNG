<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use App\User;
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

        $data['notifications'] = $user->notifications()->where('start_date', '<=', date('Y-m-d'))->where('end_date', '>=', date('Y-m-d'))->get();

        $opened = getOrdersByStatus($user, 'open');
        $opened = $opened->sort(function($a, $b) {
            return strcmp($a->end, $b->end);
        });

        $data['opened'] = $opened;

        $shipping = getOrdersByStatus($user, 'closed');
        $shipping = $shipping->sort(function($a, $b) {
            return strcmp($a->shipping, $b->shipping);
        });
        $data['shipping'] = $shipping;

        return view('pages.dashboard', $data);
    }

    public function postVerify(Request $request)
    {
        $password = $request->input('password');
        $user = $request->user();
        $test = Auth::attempt(['username' => $user->username, 'password' => $password]);
        if ($test) {
            return 'ok';
        }
        else {
            return 'ko';
        }
    }
}
