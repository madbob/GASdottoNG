<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use DB;

use App\Services\VariantsService;
use App\Exceptions\AuthException;

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
            'endpoint' => 'variants',
            'service' => $service
        ]);
    }

    public function create(Request $request)
    {
        $product = Product::findOrFail($request->input('product_id'));
        $this->ensureAuth(['supplier.modify' => $product->supplier]);
        return view('variant.edit', ['product' => $product, 'variant' => null]);
    }

    public function edit(Request $request, $id)
    {
        try {
            $variant = $this->service->show($id);
            return view('variant.edit', ['product' => $variant->product, 'variant' => $variant]);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
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

    public function updateMatrix(Request $request, $id)
    {
        DB::beginTransaction();

        $product = Product::findOrFail($id);
        $this->ensureAuth(['supplier.modify' => $product->supplier]);

        $combinations = $request->input('combination');
        $codes = $request->input('code', []);
        $actives = $request->input('active', []);
        $prices = $request->input('price_offset', []);
        $weights = $request->input('weight_offset', []);

        foreach($combinations as $index => $combination) {
            $combo = VariantCombo::byValues(explode(',', $combination));
            $combo->code = $codes[$index];
            $combo->active = in_array($combo->id, $actives);
            $combo->price_offset = $prices[$index];
            $combo->weight_offset = $weights[$index] ?? 0;
            $combo->save();
        }

        DB::commit();

        return $this->successResponse();
    }
}
