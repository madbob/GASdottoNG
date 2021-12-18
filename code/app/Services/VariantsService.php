<?php

namespace App\Services;

use Auth;
use Log;
use DB;

use App\Events\VariantChanged;
use App\Product;
use App\Variant;
use App\VariantValue;
use App\BookedProductVariant;

class VariantsService extends BaseService
{
    public function show($id)
    {
        $variant = Variant::findOrFail($id);
        $this->ensureAuth(['supplier.modify' => $variant->product->supplier]);
        return $variant;
    }

    private function updateVariantValue($id, $variant, $value)
    {
        if (empty($id)) {
            $val = new VariantValue();
        }
        else {
            $val = VariantValue::find($id);
        }

        $val->variant_id = $variant->id;
        $val->value = $value;
        $val->save();
        return $val;
    }

    private function retrieveVariant($request)
    {
        $variant_id = $request['variant_id'] ?? '';
        if (!empty($variant_id)) {
            $variant = Variant::findOrFail($variant_id);
        }
        else {
            $variant = new Variant();
            $product_id = $request['product_id'] ?? '';
            $product = Product::findOrFail($product_id);
            $variant->product_id = $product->id;
        }

        return $variant;
    }

    public function store(array $request)
    {
        DB::beginTransaction();

        $variant = $this->retrieveVariant($request);
        $this->ensureAuth(['supplier.modify' => $variant->product->supplier]);

        $this->setIfSet($variant, $request, 'name');
        $variant->save();

        $ids = $request['id'] ?? [];
        $new_values = $request['value'] ?? [];
        $new_ids = [];

        foreach($new_values as $i => $value) {
            $value = trim($value);
            if (empty($value)) {
                continue;
            }

            $id = $ids[$i];
            $val = $this->updateVariantValue($id, $variant, $value);
            $new_ids[] = $val->id;
        }

        $values_to_remove = VariantValue::where('variant_id', '=', $variant->id)->whereNotIn('id', $new_ids)->get();
        foreach($values_to_remove as $vtr) {
            $vtr->delete();
        }

        VariantChanged::dispatch($variant);

        DB::commit();

        return $variant;
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        $variant = Variant::findOrFail($id);
        $this->ensureAuth(['supplier.modify' => $variant->product->supplier]);
        $variant->delete();

        DB::commit();

        return $variant;
    }
}
