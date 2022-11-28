<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;
use Auth;
use Hash;
use Artisan;

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
        $user->saveQuietly();

        /*
            In mancanza d'altro, eseguo qui lo scheduling delle operazioni
            periodiche
        */
        Artisan::call('check:fees');
        Artisan::call('close:orders');
        Artisan::call('open:orders');
        Artisan::call('remind:orders');
        Artisan::call('check:system_notices');

        if ($user->gas->getConfig('es_integration')) {
            Artisan::call('check:remote_products');
        }

        $data['notifications'] = $user->notifications()->where('start_date', '<=', date('Y-m-d'))->where('end_date', '>=', date('Y-m-d'))->get();

        $opened = Aggregate::getByStatus($user, 'open');
        $opened = $opened->sort(function($a, $b) {
            return strcmp($a->end, $b->end);
        });
        $data['opened'] = $opened;

        $shipping = Aggregate::getByStatus($user, 'closed');
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
        if ($test)
            return 'ok';
        else
            return 'ko';
    }
}
