<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use DB;
use Auth;
use Theme;

use App\Supplier;

class SuppliersController extends Controller
{
	public function __construct()
	{
		$this->middleware('auth');
	}

	public function index()
	{
		$data['suppliers'] = Supplier::orderBy('name', 'asc')->get();
		return Theme::view('pages.suppliers', $data);
	}

	public function store(Request $request)
	{
		DB::beginTransaction();

		$user = Auth::user();
		if ($user->gas->userCan('supplier.add') == false)
			return $this->errorResponse('Non autorizzato');

		$s = new Supplier();
		$s->name = $request->input('name');
		$s->id = str_slug($s->name);
		$s->taxcode = $request->input('taxcode');
		$s->vat = $request->input('vat');
		$s->description = $request->input('description');
		$s->website = $request->input('website');
		$s->save();

		$s->userPermit('supplier.modify|supplier.orders|supplier.bookings|supplier.shippings', $user);

		return $this->successResponse([
			'id' => $s->id,
			'name' => $s->name,
			'header' => $s->printableHeader(),
			'url' => url('suppliers/' . $s->id)
		]);
	}

	public function show($id)
	{
		$s = Supplier::findOrFail($id);

		if ($s->userCan('supplier.modify'))
			return Theme::view('supplier.edit', ['supplier' => $s]);
		else
			return Theme::view('supplier.show', ['supplier' => $s]);
	}

	public function update(Request $request, $id)
	{
		DB::beginTransaction();

		$s = Supplier::findOrFail($id);

		if ($s->userCan('supplier.modify') == false)
			return $this->errorResponse('Non autorizzato');

		$s->name = $request->input('name');
		$s->taxcode = $request->input('taxcode');
		$s->vat = $request->input('vat');
		$s->description = $request->input('description');
		$s->save();

		return $this->successResponse(['id' => $s->id, 'name' => $s->name]);
	}

	public function destroy($id)
	{
		DB::beginTransaction();

		$s = Supplier::findOrFail($id);

		if ($s->userCan('supplier.modify') == false)
			return $this->errorResponse('Non autorizzato');

		$s->deletePermissions();
		$s->delete();
		return $this->successResponse();
	}
}
