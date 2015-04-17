<?php namespace App\Http\Controllers;

class ViewController extends Controller {

	public function __construct()
	{
		$this->middleware('auth');
	}

	public function index($type)
	{
		if (file_exists(public_path() . '/js/' . $type . '.js'))
			return view('default', ['type' => $type]);
		else
			abort(404, 'Pagina non trovata');
	}

}
