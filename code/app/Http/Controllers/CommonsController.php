<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

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
		$data['opened'] = Aggregate::getByStatus('open');
		$data['shipping'] = Aggregate::getByStatus('shipping');

		return Theme::view('pages.dashboard', $data);
	}
}
