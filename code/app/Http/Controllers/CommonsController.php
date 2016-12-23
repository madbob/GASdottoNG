<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Auth;
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
        $data['notifications'] = $user->notifications;
        $data['opened'] = Aggregate::getByStatus('open');
        $data['shipping'] = Aggregate::getByStatus('closed');

        return Theme::view('pages.dashboard', $data);
    }
}
