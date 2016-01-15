<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use DB;
use Auth;
use Theme;

use App\Supplier;
use App\Order;
use App\Product;
use App\Variant;
use App\VariantValue;

class ProductsController extends Controller
{
	public function __construct()
	{
		$this->middleware('auth');
	}

	private function basicReadFromRequest(&$obj, $request)
	{
		$obj->name = $request->input('name');
		$obj->description = $request->input('description');
		$obj->price = $request->input('price');
		$obj->transport = $request->input('transport');
		$obj->category_id = $request->input('category_id');
		$obj->measure_id = $request->input('measure_id');
	}

	public function store(Request $request)
	{
		DB::beginTransaction();

		$supplier = Supplier::findOrFail($request->input('supplier_id'));
		if ($supplier->userCan('supplier.modify') == false)
			return $this->errorResponse('Non autorizzato');

		$p = new Product();
		$p->supplier_id = $supplier->id;
		$p->active = true;
		$this->basicReadFromRequest($p, $request);
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

	public function update(Request $request, $id)
	{
		DB::beginTransaction();

		$p = Product::findOrFail($id);

		if ($p->supplier->userCan('supplier.modify') == false)
			return $this->errorResponse('Non autorizzato');

		/*
			Se il prodotto è già stato precedentemente incluso in un
			ordine, ne salvo una copia e lascio l'originale intonso
			per conservare le informazioni storiche
		*/
		$cloned = null;

		$master_order = Order::whereHas('products', function($query) use ($p) {
			$query->where('id', $p->id);
		})->first();

		if ($master_order != null) {
			$new_p = new Product();
			$new_p->id = $p->nextId();
			$new_p->supplier_id = $p->supplier_id;
			$new_p->active = $p->active;
			$new_p->previous_id = $p->id;
			$cloned = $p;
			$p = $new_p;
		}

		$this->basicReadFromRequest($p, $request);
		$p->active = $request->has('active');
		$p->partitioning = $request->input('partitioning');
		$p->variable = $request->has('variable') ? true : false;
		$p->multiple = $request->input('multiple');
		$p->minimum = $request->input('minimum');
		$p->totalmax = $request->input('totalmax');
		$p->save();

		/*
			In caso di prodotto copiato (vedi sopra) duplico anche
			tutte le varianti
		*/
		if ($cloned !== null) {
			foreach($cloned->variants as $variant) {
				$new_var = new Variant();
				$new_var->name = $variant->name;
				$new_var->has_offset = $variant->has_offset;
				$new_var->product_id = $p->id;
				$new_var->save();

				foreach($variant->values as $value) {
					$new_val = new VariantValue();
					$new_val->value = $value->value();
					$new_val->price_offset = $value->price_offset;
					$new_val->variant_id = $new_var->id;
					$new_val->save();
				}
			}
		}

		return $this->successResponse([
			'id' => $p->id,
			'header' => $p->printableHeader(),
			'url' => url('products/' . $p->id)
		]);
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
