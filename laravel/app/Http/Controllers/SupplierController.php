<?php namespace App\Http\Controllers;

use App\Supplier;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

class SupplierController extends Controller {

	public function index()
	{
		$ret = array();
		$suppliers = Supplier::get();

		foreach ($suppliers as $supplier) {
			$ret[] = (object) array(
				'id' => $supplier->id,
				'name' => $supplier->name
			);
		}

		return response(json_encode($ret), 200)->header('Content-Type', 'application/json');
	}

	public function create()
	{
		//
	}

	public function store()
	{
		// Pippo 
	}

	public function show($id)
	{
		$supplier = Supplier::find($id);
		if ($supplier->exists()) {
			return response()->json([
				'id' => $supplier->id,
				'name' => $supplier->name,
				'mail' => $supplier->mail,
				'phone' => $supplier->phone,
				'description' => $supplier->description
			]);
		}
		else {
			abort(404, 'Fornitore non trovato');
		}
	}

	public function edit($id)
	{
		//
	}

	public function update($id)
	{
		//
	}

	public function destroy($id)
	{
		//
	}

}
