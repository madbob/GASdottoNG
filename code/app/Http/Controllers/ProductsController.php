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

	private function handleSaveVariants($product, $request)
	{
		$new_variants = $request->input('variantname', []);
		$all_new_values = $request->input('variantvalues', []);
		$existing_variants = $product->variants;
		$index = 0;
		$matching_variants = [];

		foreach($new_variants as $nv) {
			$variant_found = false;
			$new_values = explode(',', $all_new_values[$index]);

			foreach($existing_variants as $ev) {
				if ($ev->name == $nv) {
					$variant_found = true;
					$matching_variants[] = $ev->id;

					$existing_values = $ev->values;
					$matching_values = [];

					foreach($new_values as $value) {
						$value_found = false;

						foreach($existing_values as $evalue) {
							if ($value == $evalue->value) {
								$value_found = true;
								$matching_values[] = $evalue->id;
							}
						}

						if ($value_found == false) {
							$val = new VariantValue();
							$val->value = $value;
							$val->variant_id = $ev->id;
							$val->save();
							$matching_values[] = $val->id;
						}
					}

					VariantValue::where('variant_id', '=', $ev->id)->whereNotIn('id', $matching_values)->delete();
				}
			}

			if ($variant_found == false) {
				$v = new Variant();
				$v->name = $nv;
				$v->product_id = $product->id;
				$v->save();

				foreach($new_values as $value) {
					$val = new VariantValue();
					$val->value = $value;
					$val->variant_id = $v->id;
					$val->save();
				}

				$matching_variants[] = $v->id;
			}

			$index++;
		}

		$remove_variants = Variant::where('product_id', '=', $product->id)->whereNotIn('id', $matching_variants)->get();
		foreach($remove_variants as $rv) {
			$rv->values()->delete();
			$rv->delete();
		}
	}

	public function store(Request $request)
	{
		DB::beginTransaction();

		$supplier = Supplier::findOrFail($request->input('supplier_id'));
		if ($supplier->userCan('supplier.modify') == false)
			return $this->errorResponse('Non autorizzato');

		$p = new Product();
		$p->supplier_id = $supplier->id;
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
		$master_order = Order::whereHas('products', function($query) use ($p) {
			$query->where('id', $p->id);
		})->first();

		if ($master_order != null) {
			$new_p = new Product();
			$new_p->id = $p->nextId();
			$new_p->supplier_id = $p->supplier_id;
			$new_p->previous_id = $p->id;
			$p = $new_p;
		}

		$this->basicReadFromRequest($p, $request);
		$p->partitioning = $request->input('partitioning');
		$p->variable = $request->has('variable') ? true : false;
		$p->multiple = $request->input('multiple');
		$p->minimum = $request->input('minimum');
		$p->maximum = $request->input('maximum');
		$p->save();

		$this->handleSaveVariants($p, $request);

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
