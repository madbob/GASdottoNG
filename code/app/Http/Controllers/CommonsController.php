<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use DB;
use Auth;
use Theme;

use App\Notification;
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
