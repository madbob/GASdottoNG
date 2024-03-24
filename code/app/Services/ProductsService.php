<?php

namespace App\Services;

use Auth;
use Log;
use DB;

use App\User;
use App\Supplier;
use App\Product;
use App\Category;
use App\Measure;
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
        }
        else {
            $this->transformAndSetIfSet($product, $request, 'portion_quantity', 'enforceNumber');

            /*
                Per le unità di misura non discrete assumo che il peso sia 1, se
                non diversamente specificato, perché nella stragrande
                maggioranza dei casi si tratta di Chili
            */
            if (blank($product->weight) || $product->weight == 0) {
                $product->weight = 1;
            }
        }
    }

    private function setCommonAttributes(&$product, $request)
    {
        $this->setIfSet($product, $request, 'name');
        $this->setIfSet($product, $request, 'description');
        $this->transformAndSetIfSet($product, $request, 'price', 'enforceNumber');

        $this->setIfSet($product, $request, 'category_id');
        if (empty($product->category_id)) {
            $product->category_id = Category::defaultValue();
        }

        $this->setIfSet($product, $request, 'measure_id');
        if (empty($product->measure_id)) {
            $product->measure_id = Measure::defaultValue();
        }

        $this->transformAndSetIfSet($product, $request, 'vat_rate_id', function($value) {
            return $value != 0 ? $value : null;
        });

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

    private function duplicateVariants($from, $to)
    {
        foreach($from->variants as $old_variant) {
            $new_variant = $old_variant->replicate();
            $new_variant->id = '';
            $new_variant->product_id = $to->id;
            $new_variant->save();

            foreach($old_variant->values as $old_value) {
                $new_value = $old_value->replicate();
                $new_value->id = '';
                $new_value->variant_id = $new_variant->id;
                $new_value->save();
            }
        }
    }

    private function duplicateModifiers($from, $to)
    {
        foreach($from->modifiers as $old_modifier) {
            $new_modifier = $old_modifier->replicate();
            unset($new_modifier->id);
            $new_modifier->target_id = $to->id;
            $new_modifier->target_type = get_class($to);
            $new_modifier->save();
        }
    }

    private function checkDuplication($product, $request)
    {
        if (isset($request['duplicating_from'])) {
            $original_product_id = $request['duplicating_from'];
            $original_product = Product::find($original_product_id);

            $this->duplicateVariants($original_product, $product);
            $this->duplicateModifiers($original_product, $product);
        }
        else {
            $product->active = true;
        }

        $product->save();
        return $product;
    }

    private function commonsSaving($product, $request)
    {
        DB::beginTransaction();

        $this->setCommonAttributes($product, $request);
        $product->save();
        handleFileUpload($request, $product, 'picture');

        DB::commit();
        return $product;
    }

    public function store(array $request)
    {
        $supplier = Supplier::findOrFail($request['supplier_id']);
        $this->ensureAuth(['supplier.modify' => $supplier]);

        $product = new Product();
        $product->supplier_id = $supplier->id;

        $product = $this->commonsSaving($product, $request);
        $product = $this->checkDuplication($product, $request);

        return $product;
    }

    public function update($id, array $request)
    {
        $product = $this->show($id);
        $this->ensureAuth(['supplier.modify' => $product->supplier]);
        $product = $this->commonsSaving($product, $request);
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

            $request = request();

            foreach($request->all() as $key => $value) {
                if (str_starts_with($key, 'order_')) {
                    if ($value == 'leave') {
                        $order_id = substr($key, strpos($key, '_') + 1);
                        $order = fromInlineId($order_id);
                        $order->detachProduct($product);
                    }
                }
            }

            $product->delete();
            return $product;
        });

        return $product;
    }
}
