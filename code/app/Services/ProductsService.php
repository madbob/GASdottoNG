<?php

namespace App\Services;

use App\Exceptions\AuthException;
use App\Exceptions\IllegalArgumentException;

use Auth;
use Log;
use DB;

use App\User;
use App\Supplier;
use App\Product;
use App\Role;

class ProductsService extends BaseService
{
    public function list($term = '', $all = false)
    {
        /* TODO */
    }

    public function show($id)
    {
        return Product::withTrashed()->with('variants')->with('variants.values')->findOrFail($id);
    }

    private function enforceMeasure($product, $request)
    {
        if ($product->measure->discrete) {
            $product->portion_quantity = 0;
            $product->variable = false;
        }
        else {
            $this->transformAndSetIfSet($product, $request, 'portion_quantity', 'enforceNumber');
            $product->variable = isset($request['variable']);
        }

        return $product;
    }

    private function setCommonAttributes($product, $request)
    {
        $this->setIfSet($product, $request, 'name');
        $this->setIfSet($product, $request, 'description');
        $this->transformAndSetIfSet($product, $request, 'price', 'enforceNumber');
        $this->transformAndSetIfSet($product, $request, 'transport', 'enforceNumber');

        $this->setIfSet($product, $request, 'category_id');
        if (empty($product->category_id))
            $product->category_id = 'non-specificato';

        $this->setIfSet($product, $request, 'measure_id');
        if (empty($product->measure_id))
            $product->measure_id = 'non-specificato';

        $this->transformAndSetIfSet($product, $request, 'discount', 'normalizePercentage');

        $this->transformAndSetIfSet($product, $request, 'vat_rate_id', function($value) {
            if ($value != 0)
                return $value;
            else
                return null;
        });
    }

    public function store(array $request)
    {
        $supplier = Supplier::findOrFail($request['supplier_id']);
        $this->ensureAuth(['supplier.modify' => $supplier]);

        $product = new Product();
        $product->supplier_id = $supplier->id;
        $product->active = true;

        DB::transaction(function () use ($product, $request) {
            $this->setCommonAttributes($product, $request);
            $product->save();
        });

        return $product;
    }

    public function update($id, array $request)
    {
        $product = $this->show($id);
        $this->ensureAuth(['supplier.modify' => $product->supplier]);

        DB::transaction(function () use ($product, $request) {
            $this->setCommonAttributes($product, $request);

            $product->active = (isset($request['active']) && $request['active'] !== false);
            $this->setIfSet($product, $request, 'supplier_code');
            $this->transformAndSetIfSet($product, $request, 'package_size', 'enforceNumber');
            $this->transformAndSetIfSet($product, $request, 'multiple', 'enforceNumber');
            $this->transformAndSetIfSet($product, $request, 'min_quantity', 'enforceNumber');
            $this->transformAndSetIfSet($product, $request, 'max_quantity', 'enforceNumber');
            $this->transformAndSetIfSet($product, $request, 'max_available', 'enforceNumber');
            $product = $this->enforceMeasure($product, $request);
            $product->save();

            if (isset($request['picture'])) {
                saveFile($request['picture'], $product, 'picture');
            }
        });

        return $product;
    }

    public function duplicate($id)
    {
        $original = $this->show($id);
        $this->ensureAuth(['supplier.modify' => $original->supplier]);

        $product = $original->replicate();
        $product->id = '';
        $product->name = 'Copia di ' . $product->name;
        $product->save();
        return $product;
    }

    public function picture($id)
    {
        $product = Product::findOrFail($id);
        return downloadFile($product, 'picture');
    }

    public function destroy($id)
    {
        $product = DB::transaction(function() use ($id) {
            $product = $this->show($id);
            $this->ensureAuth(['supplier.modify' => $product->supplier]);
            $product->delete();
            return $product;
        });

        return $product;
    }
}
