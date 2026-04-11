<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CommonsController extends Controller
{
    public function getIndex()
    {
        $user = Auth::user();
        $user->last_login = date('Y-m-d G:i:s');
        $user->save();

        $data['notifications'] = $user->notifications()->where(function($query) {
            $now = date('Y-m-d');
            $query->where('start_date', '<=', $now)->where('end_date', '>=', $now);
        })->get();

        $opened = getOrdersByStatus($user, 'open');
        $opened = $opened->sort(function ($a, $b) {
            return strcmp($a->end, $b->end);
        });

        $data['opened'] = $opened;

        $shipping = getOrdersByStatus($user, 'closed');
        $shipping = $shipping->sort(function ($a, $b) {
            return strcmp($a->shipping, $b->shipping);
        });

        $data['shipping'] = $shipping;

        return view('pages.dashboard', $data);
    }

    public function postVerify(Request $request)
    {
        $password = $request->input('password');
        $user = $request->user();

        if (!Hash::check($password, $user->password)) {
            return 'ko';
        }
        else {
            $request->session()->passwordConfirmed();
            return 'ok';
        }
    }
}
