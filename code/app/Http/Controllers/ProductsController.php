<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use DB;
use Auth;
use Theme;

use App\Supplier;
use App\Product;

class ProductsController extends Controller
{
	public function __construct()
	{
		$this->middleware('auth');
	}

	public function store(Request $request)
	{
		DB::beginTransaction();

		$supplier = Supplier::findOrFail($request->input('supplier_id'));
		if ($supplier->userCan('supplier.modify') == false)
			return $this->errorResponse('Non autorizzato');

		$p = new Product();
		$p->name = $request->input('name');
		$p->id = $supplier->id . '::' . str_slug($p->name);
		$p->description = $request->input('description');
		$p->price = $request->input('price');
		$p->transport = $request->input('transport');
		$p->supplier_id = $supplier->id;
		$p->category_id = $request->input('category_id');
		$p->measure_id = $request->input('measure_id');
		$p->save();

		return $this->successResponse([
			'id' => $p->id,
			'name' => $p->name,
			'header' => $p->printableHeader(),
			'url' => url('products/' . $p->id)
		]);
	}

	public function show($id)
	{
		$p = Product::findOrFail($id);

		if ($p->supplier->userCan('supplier.modify'))
			return Theme::view('product.edit', ['product' => $p]);
		else
			return Theme::view('product.show', ['product' => $p]);
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
		DB::beginTransaction();

		$p = Product::findOrFail($id);

		if ($p->supplier->userCan('supplier.modify') == false)
			return $this->errorResponse('Non autorizzato');

		$p->delete();
		return $this->successResponse();
	}
}
