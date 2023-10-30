<?php

namespace App\Services;

use Auth;
use Log;
use DB;

use App\User;
use App\Supplier;
use App\Product;
use App\Role;

class ProductsService extends BaseService
{
    public function search($supplier_id, $term)
    {
        $ret = (object) [
            'results' => [
                (object) [
                    'id' => 0,
                    'text' => _i('Nessuno'),
                ]
            ]
        ];

        $supplier = Supplier::findOrFail($supplier_id);
        $term = sprintf('%%%s%%', $term);
        $products = $supplier->products()->where('name', 'like', $term)->orderBy('name', 'asc')->get();

        foreach($products as $prod) {
            $ret->results[] = (object) [
                'id' => $prod->id,
                'text' => $prod->printableName(),
            ];
        }

        return $ret;
    }

    public function show($id)
    {
        return Product::withTrashed()->with('variants')->with('variants.values')->findOrFail($id);
    }

    private function enforceMeasure(&$product, $request)
    {
        if ($product->measure->discrete) {
            $product->portion_quantity = 0;
            $product->variable = false;
        }
        else {
            $this->transformAndSetIfSet($product, $request, 'portion_quantity', 'enforceNumber');
            $this->boolIfSet($product, $request, 'variable');
        }
    }

    private function setCommonAttributes(&$product, $request)
    {
        $this->setIfSet($product, $request, 'name');
        $this->setIfSet($product, $request, 'description');
        $this->transformAndSetIfSet($product, $request, 'price', 'enforceNumber');

        $this->setIfSet($product, $request, 'category_id');
        if (empty($product->category_id)) {
            $product->category_id = 'non-specificato';
        }

        $this->setIfSet($product, $request, 'measure_id');
        if (empty($product->measure_id)) {
            $product->measure_id = 'non-specificato';
        }

        $this->transformAndSetIfSet($product, $request, 'vat_rate_id', function($value) {
            if ($value != 0) {
                return $value;
            }
            else {
                return null;
            }
        });
    }

    private function setUncommonAttributes(&$product, $request)
    {
        $this->boolIfSet($product, $request, 'active');
        $this->setIfSet($product, $request, 'supplier_code');

        /*
            Questo viene definito in ProductsController::massiveUpdate()
        */
        $this->setIfSet($product, $request, 'sorting');

        $this->transformAndSetIfSet($product, $request, 'weight', 'enforceNumber');
        $this->transformAndSetIfSet($product, $request, 'package_size', 'enforceNumber');
        $this->transformAndSetIfSet($product, $request, 'multiple', 'enforceNumber');
        $this->transformAndSetIfSet($product, $request, 'min_quantity', 'enforceNumber');
        $this->transformAndSetIfSet($product, $request, 'max_quantity', 'enforceNumber');
        $this->transformAndSetIfSet($product, $request, 'max_available', 'enforceNumber');
        $this->transformAndSetIfSet($product, $request, 'global_min', 'enforceNumber');
        $this->enforceMeasure($product, $request);
    }

    public function store(array $request)
    {
        $supplier = Supplier::findOrFail($request['supplier_id']);
        $this->ensureAuth(['supplier.modify' => $supplier]);

        $product = new Product();
        $product->supplier_id = $supplier->id;

        if (!isset($request['duplicating_from'])) {
            $product->active = true;
        }

        DB::transaction(function () use ($product, $request) {
            $this->setCommonAttributes($product, $request);
            $product->save();
        });

        if (isset($request['duplicating_from'])) {
            $this->setUncommonAttributes($product, $request);
            $product->save();

            $original_product_id = $request['duplicating_from'];
            $original_product = Product::find($original_product_id);

            foreach($original_product->variants as $old_variant) {
                $new_variant = $old_variant->replicate();
                $new_variant->id = '';
                $new_variant->product_id = $product->id;
                $new_variant->save();

                foreach($old_variant->values as $old_value) {
                    $new_value = $old_value->replicate();
                    $new_value->id = '';
                    $new_value->variant_id = $new_variant->id;
                    $new_value->save();
                }
            }

            foreach($original_product->modifiers as $old_modifier) {
                $new_modifier = $old_modifier->replicate();
                unset($new_modifier->id);
                $new_modifier->target_id = $product->id;
                $new_modifier->target_type = get_class($product);
                $new_modifier->save();
            }
        }

        return $product;
    }

    public function update($id, array $request)
    {
        $product = $this->show($id);
        $this->ensureAuth(['supplier.modify' => $product->supplier]);

        DB::transaction(function () use ($product, $request) {
            $this->setCommonAttributes($product, $request);
            $this->setUncommonAttributes($product, $request);
            $product->save();
            handleFileUpload($request, $product, 'picture');
        });

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
