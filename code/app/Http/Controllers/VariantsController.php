<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use App\Product;
use App\Variant;
use App\VariantValue;

class VariantsController extends Controller
{
    public function __construct()
    {
        $this->commonInit([
            'reference_class' => 'App\\Variant'
        ]);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        $product_id = $request->input('product_id');
        $product = Product::findOrFail($product_id);

        if ($request->user()->can('supplier.modify', $product->supplier) == false) {
            return $this->errorResponse('Non autorizzato');
        }

        $variant_id = $request->input('variant_id');
        if (!empty($variant_id)) {
            $variant = Variant::findOrFail($variant_id);
        } else {
            $variant = new Variant();
        }

        $variant->name = $request->input('name');
        $variant->product_id = $product_id;
        $variant->has_offset = $request->has('has_offset');
        $variant->save();

        $new_values = $request->input('value', []);
        $new_offsets = $request->input('price_offset', []);
        $existing_values = $variant->values;
        $matching_values = [];

        for ($i = 0; $i < count($new_values); ++$i) {
            $value = $new_values[$i];
            if (empty($value))
                continue;

            $offset = $new_offsets[$i];
            $value_found = false;

            foreach ($existing_values as $evalue) {
                if ($value == $evalue->value) {
                    $value_found = true;
                    $matching_values[] = $evalue->id;

                    if ($variant->has_offset == true && $evalue->price_offset != $offset) {
                        $evalue->price_offset = $offset;
                        $evalue->save();
                    } elseif ($variant->has_offset == false && $evalue->price_offset != 0) {
                        $evalue->price_offset = 0;
                        $evalue->save();
                    }
                }
            }

            if ($value_found == false) {
                $val = new VariantValue();
                $val->value = $value;

                if ($variant->has_offset) {
                    $val->price_offset = $offset;
                } else {
                    $val->price_offset = 0;
                }

                $val->variant_id = $variant->id;
                $val->save();
                $matching_values[] = $val->id;
            }
        }

        VariantValue::where('variant_id', '=', $variant->id)->whereNotIn('id', $matching_values)->delete();

        /*
            Solo una singola variante per prodotto può avere la
            "differenza prezzo", se viene attivata sulla variante
            correntemente salvata la tolgo dall'eventuale altra
            esistente
        */
        if ($variant->has_offset) {
            foreach ($product->variants as $v) {
                if ($v->id != $variant->id) {
                    if ($v->has_offset) {
                        $v->has_offset = false;
                        $v->save();
                        $v->values()->update('price_offset', 0);
                        break;
                    }
                }
            }
        }

        DB::commit();

        return view('product.variantseditor', ['product' => $product]);
    }

    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        $variant = Variant::findOrFail($id);

        $product = $variant->product;
        if ($request->user()->can('supplier.modify', $product->supplier) == false) {
            return $this->errorResponse('Non autorizzato');
        }

        $variant->values()->delete();
        $variant->delete();

        DB::commit();

        return view('product.variantseditor', ['product' => $product]);
    }
}
