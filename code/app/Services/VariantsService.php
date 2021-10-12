<?php

namespace App\Services;

use App\Exceptions\AuthException;
use App\Exceptions\IllegalArgumentException;

use Auth;
use Log;
use DB;

use App\Product;
use App\Variant;
use App\VariantValue;
use App\BookedProductVariant;

class VariantsService extends BaseService
{
    private function removeFromBooked($type, $id)
    {
        $booked = BookedProductVariant::whereHas('components', function($query) use ($type, $id) {
            $query->where($type, $id);
        })->with('components')->get();

        foreach($booked as $b) {
            if ($b->components->count() == 1) {
                $b->components->first()->delete();
                $b->delete();
            }
            else {
                foreach($b->components as $component) {
                    if ($component->$type == $id) {
                        $component->delete();
                    }
                }
            }
        }
    }

    public function show($id)
    {
        $variant = Variant::findOrFail($id);
        $this->ensureAuth(['supplier.modify' => $variant->product->supplier]);
        return $variant;
    }

    public function store(array $request)
    {
        DB::beginTransaction();

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

            if (empty($id)) {
                $val = new VariantValue();
                $val->variant_id = $variant->id;
            }
            else {
                $val = VariantValue::find($id);
            }

            $val->value = $value;
            $val->save();

            $new_ids[] = $val->id;
        }

        $values_to_remove = VariantValue::where('variant_id', '=', $variant->id)->whereNotIn('id', $new_ids)->get();
        foreach($values_to_remove as $vtr) {
            $this->removeFromBooked('value_id', $vtr->id);
            $vtr->delete();
        }

        $variant->product->reviewCombos();

        DB::commit();

        return $variant;
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        $variant = Variant::findOrFail($id);

        $product = $variant->product;
        $this->ensureAuth(['supplier.modify' => $product->supplier]);

        $this->removeFromBooked('variant_id', $variant->id);
        $variant->values()->delete();
        $variant->delete();

        $product->reviewCombos();

        DB::commit();

        return $variant;
    }
}
