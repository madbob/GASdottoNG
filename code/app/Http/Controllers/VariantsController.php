<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;

use App\Product;
use App\Variant;
use App\VariantValue;
use App\VariantCombo;
use App\BookedProductVariant;

class VariantsController extends Controller
{
    public function __construct()
    {
        $this->commonInit([
            'reference_class' => 'App\\Variant'
        ]);
    }

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
                foreach($b->components as $component)
                    if ($component->$type == $id)
                        $component->delete();
            }
        }
    }

    private function reviewCombos($product)
    {
        $combos = [[]];
        $values = [];
        $all_values = [];

        foreach($product->variants as $variant) {
            $variant_values = [];

            foreach($variant->values as $value) {
                $variant_values[] = $value->id;
                $all_values[] = $value->id;
            }

            $values[] = $variant_values;
        }

        $length = count($values);

        for ($count = 0; $count < $length; $count++) {
            $tmp = [];

            foreach ($combos as $v1) {
                foreach ($values[$count] as $v2) {
                    $tmp[] = array_merge($v1, [$v2]);
                }
            }

            $combos = $tmp;
        }

        $valid_ids = [];

        foreach($combos as $combo) {
            $vc = VariantCombo::byValues($combo);
            if (is_null($vc)) {
                $vc = new VariantCombo();
                $vc->save();
                $vc->values()->sync($combo);
            }

            $valid_ids[] = $vc->id;
        }

        VariantCombo::whereHas('values', function($query) use ($all_values) {
            $query->whereIn('variant_value_id', $all_values);
        })->whereNotIn('id', $valid_ids)->delete();
    }

    public function create(Request $request)
    {
        $product = Product::findOrFail($request->input('product_id'));
        return view('variant.edit', ['product' => $product, 'variant' => null]);
    }

    public function edit(Request $request, $id)
    {
        $variant = Variant::findOrFail($id);
        return view('variant.edit', ['product' => $variant->product, 'variant' => $variant]);
    }

    public function show(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        return view('variant.editor', ['product' => $product, 'duplicate' => false]);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        $variant_id = $request->input('variant_id');
        if (!empty($variant_id)) {
            $variant = Variant::findOrFail($variant_id);
        }
        else {
            $variant = new Variant();
            $product_id = $request->input('product_id');
            $product = Product::findOrFail($product_id);
            $variant->product_id = $product->id;
        }

        if ($request->user()->can('supplier.modify', $variant->product->supplier) == false) {
            return $this->errorResponse(_i('Non autorizzato'));
        }

        $variant->name = $request->input('name');
        $variant->save();

        $new_values = $request->input('value', []);
        $existing_values = $variant->values;
        $matching_values = [];

        for ($i = 0; $i < count($new_values); ++$i) {
            $value = $new_values[$i];
            if (empty($value)) {
                continue;
            }

            $value_found = false;

            foreach ($existing_values as $evalue) {
                if ($value == $evalue->value) {
                    $value_found = true;
                    $matching_values[] = $evalue->id;
                }
            }

            if ($value_found == false) {
                $val = new VariantValue();
                $val->value = $value;
                $val->variant_id = $variant->id;
                $val->save();
                $matching_values[] = $val->id;
            }
        }

        $values_to_remove = VariantValue::where('variant_id', '=', $variant->id)->whereNotIn('id', $matching_values)->get();
        foreach($values_to_remove as $vtr) {
            $this->removeFromBooked('value_id', $vtr->id);
            $vtr->delete();
        }

        $this->reviewCombos($product);

        DB::commit();

        return $this->successResponse();
    }

    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        $variant = Variant::findOrFail($id);

        $product = $variant->product;
        if ($request->user()->can('supplier.modify', $product->supplier) == false) {
            return $this->errorResponse(_i('Non autorizzato'));
        }

        $this->removeFromBooked('variant_id', $variant->id);
        $variant->values()->delete();
        $variant->delete();

        $this->reviewCombos($product);

        DB::commit();

        return $this->successResponse();
    }

    public function matrix(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        return view('variant.matrix', ['product' => $product]);
    }

    public function updateMatrix(Request $request, $id)
    {
        DB::beginTransaction();

        $product = Product::findOrFail($id);

        $combinations = $request->input('combination');
        $codes = $request->input('code', []);
        $prices = $request->input('price_offset', []);
        $weights = $request->input('weight_offset', []);

        foreach($combinations as $index => $combination) {
            $combo = VariantCombo::byValues(explode(',', $combination));
            $combo->code = $codes[$index];
            $combo->price_offset = $prices[$index];
            $combo->weight_offset = $weights[$index] ?? 0;
            $combo->save();
        }

        DB::commit();

        return $this->successResponse();
    }
}
