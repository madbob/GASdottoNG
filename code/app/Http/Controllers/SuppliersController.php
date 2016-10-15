<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use DB;
use Auth;
use Theme;
use PDF;

use App\Supplier;

class SuppliersController extends Controller
{
	public function __construct()
	{
		$this->middleware('auth');
	}

	private function basicReadFromRequest(&$obj, $request)
	{
		$obj->name = $request->input('name');
		$obj->taxcode = $request->input('taxcode');
		$obj->vat = $request->input('vat');
		$obj->description = $request->input('description');
		$obj->website = $request->input('website');
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
		$this->basicReadFromRequest($s, $request);
		$s->save();

		$s->userPermit('supplier.modify|supplier.orders|supplier.shippings', $user);

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

		$this->basicReadFromRequest($s, $request);
		$s->save();

		return $this->successResponse([
			'id' => $s->id,
			'header' => $s->printableHeader(),
			'url' => url('suppliers/' . $s->id)
		]);
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

	public function catalogue(Request $request, $id, $format)
	{
		$s = Supplier::findOrFail($id);

		if($format == 'pdf') {
			$html = Theme::view('documents.cataloguepdf', ['supplier' => $s])->render();
			$filename = sprintf('Listino %s.pdf', $s->name);
			PDF::SetTitle(sprintf('Listino %s del %s', $s->name, date('d/m/Y')));
			PDF::AddPage();
			PDF::writeHTML($html, true, false, true, false, '');
			PDF::Output($filename, 'D');
		}
		else if($format == 'csv') {
			$filename = sprintf('Listino %s.csv', $s->name);
			header('Content-Type: text/csv');
			header('Content-Disposition: attachment; filename=' . $filename);
			header('Cache-Control: no-cache, no-store, must-revalidate');
			header('Pragma: no-cache');
			header('Expires: 0');
			return Theme::view('documents.cataloguecsv', ['supplier' => $s]);
		}
	}
}
