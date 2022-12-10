<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;

use DB;

use App\Services\VariantsService;

use App\Product;
use App\Variant;
use App\VariantCombo;

class VariantsController extends BackedController
{
    public function __construct(VariantsService $service)
    {
        $this->middleware('auth');

        $this->commonInit([
            'reference_class' => 'App\\Variant',
            'service' => $service
        ]);
    }

    public function create(Request $request)
    {
        $product = Product::findOrFail($request->input('product_id'));
        $this->ensureAuth(['supplier.modify' => $product->supplier]);
        return view('variant.edit', ['product' => $product, 'variant' => null]);
    }

    public function edit($id)
    {
        return $this->easyExecute(function() use ($id) {
            $variant = $this->service->show($id);

            if ($variant->product->variants()->count() == 1) {
                return view('variant.editsingle', ['product' => $variant->product, 'variant' => $variant]);
            }
            else {
                return view('variant.edit', ['product' => $variant->product, 'variant' => $variant]);
            }
        });
    }

    /*
        Questo è per ricaricare dinamicamente il blocco delle varianti incluso
        nel form di modifica di un prodotto, dato l'ID del prodotto stesso
    */
    public function show(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $this->ensureAuth(['supplier.modify' => $product->supplier]);
        return view('variant.editor', ['product' => $product]);
    }

    public function matrix(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $this->ensureAuth(['supplier.modify' => $product->supplier]);
        return view('variant.matrix', ['product' => $product]);
    }

    private function transformFromSimplified($request, $product)
    {
        $original_combinations = $request->input('combination', []);

        $ids = array_map(function($item) {
            if (Str::startsWith($item, 'new_')) {
                return '';
            }
            else {
                return $item;
            }
        }, $original_combinations);

        $values = $request->input('value', []);

        $variant = $product->variants()->first();
        $variant = $this->service->store([
            'variant_id' => $variant->id,
            'id' => $ids,
            'value' => $values,
        ]);

        $combinations = [];

        foreach($values as $value) {
            $combinations[] = $variant->values()->where('value', $value)->first()->id;
        }

        /*
            Ai nuovi valori dinamicamente immessi nella tabella aggiungo un
            identificativo randomico, sul quale mi baso per risalire ai
            metadati di tale valore
        */
        $actives = [];
        $original_actives = $request->input('active', []);
        foreach ($original_actives as $ac) {
            if (Str::startsWith($ac, 'new_')) {
                $combination_index = array_search($ac, $original_combinations);
                $combination = $variant->values()->where('value', $values[$combination_index])->first()->id;
                $combo = VariantCombo::byValues(explode(',', $combination));
                $actives[] = $combo->id;
            }
            else {
                $actives[] = $ac;
            }
        }

        return [$combinations, $actives];
    }

    public function updateMatrix(Request $request, $id)
    {
        DB::beginTransaction();

        $product = Product::findOrFail($id);
        $this->ensureAuth(['supplier.modify' => $product->supplier]);

        /*
            Se il prodotto ha una sola variante, viene visualizzato il form di
            modifica "semplificato" in cui editare insieme i valori e gli
            attributi dei valori.
        */
        if ($product->variants()->count() == 1) {
            list($combinations, $actives) = $this->transformFromSimplified($request, $product);
        }
        else {
            $combinations = $request->input('combination');
            $actives = $request->input('active', []);
        }

        $codes = $request->input('code', []);
        $prices = $request->input('price_offset', []);
        $weights = $request->input('weight_offset', []);

        foreach($combinations as $index => $combination) {
            $combo = VariantCombo::byValues(explode(',', $combination));
            $combo->code = $codes[$index];
            $combo->active = in_array($combo->id, $actives);

            $combo->price_offset = $prices[$index] ?? 0;
            if (filled($combo->price_offset) == false) {
                $combo->price_offset = 0;
            }

            $combo->weight_offset = $weights[$index] ?? 0;
            if (filled($combo->weight_offset) == false) {
                $combo->weight_offset = 0;
            }

            $combo->save();
        }

        DB::commit();

        return $this->successResponse();
    }
}
