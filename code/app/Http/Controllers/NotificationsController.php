<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use DB;
use Auth;
use Theme;

use App\Notification;

class NotificationsController extends Controller
{
	public function __construct()
	{
		$this->middleware('auth');
	}

	public function index()
	{
		$user = Auth::user();
		if ($user->gas->userCan('notifications.admin') == false)
			$data['notifications'] = Notification::orderBy('created_at', 'desc')->take(20)->get();
		else
			$data['notifications'] = $user->notifications;

		return Theme::view('pages.notifications', $data);
	}

	public function create()
	{
	//
	}

	public function store(Request $request)
	{
	//
	}

	public function show($id)
	{
	//
	}

	public function edit($id)
	{
	//
	}

	public function update(Request $request, $id)
	{
	//
	}

	public function destroy($id)
	{
	//
	}
}
