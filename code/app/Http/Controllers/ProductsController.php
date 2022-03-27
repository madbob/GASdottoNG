<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BackedController;

use DB;
use Auth;

use App\Services\ProductsService;
use App\Exceptions\AuthException;
use App\Exceptions\IllegalArgumentException;

use App\Order;
use App\Product;
use App\VariantCombo;

class ProductsController extends BackedController
{
    public function __construct(ProductsService $service)
    {
        $this->service = $service;

        $this->commonInit([
            'reference_class' => 'App\\Product',
            'endpoint' => 'products',
            'service' => $service
        ]);
    }

    public function show(Request $request, $id)
    {
        $user = Auth::user();

        $format = $request->input('format', 'html');
        $product = $this->service->show($id);

        if ($format == 'html') {
            if ($user->can('supplier.modify', $product->supplier)) {
                return view('product.edit', ['product' => $product]);
            }
            else {
                return view('product.show', ['product' => $product]);
            }
        }
        else if ($format == 'modal') {
            if ($user->can('supplier.modify', $product->supplier)) {
                return view('product.editmodal', ['product' => $product]);
            }
            else {
                abort(503);
            }
        }
        elseif ($format == 'json') {
            $ret = $product->toJson();
            $ret = json_decode($ret);
            $ret->printableMeasure = $product->printableMeasure();
            return json_encode($ret);
        }
        elseif ($format == 'bookable') {
            $order = Order::find($request->input('order_id'));
            return view('booking.quantityselectrow', ['product' => $product, 'order' => $order, 'populate' => false, 'while_shipping' => true]);
        }
        else {
            abort(404);
        }
    }

    public function show_ro(Request $request, $id)
    {
        $product = $this->service->show($id);
        return view('product.show', ['product' => $product]);
    }

    public function duplicate(Request $request, $id)
    {
        $product = $this->service->show($id);
        return view('product.duplicate', ['product' => $product]);
    }

    public function store(Request $request)
    {
        try {
            $product = $this->service->store($request->all());
            return $this->commonSuccessResponse($product);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
        catch (IllegalArgumentException $e) {
            return $this->errorResponse($e->getMessage(), $e->getArgument());
        }
    }

    public function massiveUpdate(Request $request)
    {
        DB::beginTransaction();

        $product_ids = $request->input('id', []);

        foreach($product_ids as $index => $id) {
            $product = $this->service->show($id);
            $data['name'] = $request->input($id . '-name', $product->name);
            $data['price'] = $request->input($id . '-price', $product->price);
            $data['measure_id'] = $request->input($id . '-measure_id', $product->measure_id);
            $data['active'] = $request->has($id . '-active');
            $data['sorting'] = $index;
            $this->service->update($id, $data);
        }

        return $this->successResponse();
    }

    public function picture($id)
    {
        try {
            return $this->service->picture($id);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
    }

    public function price(Request $request)
    {
        $product_id = $request->input('id');
        $variant = $request->input('variant', []);
        $product = $this->service->show($product_id);

        if (empty($variant)) {
            $price = $product->price;
        }
        else {
            $combo = VariantCombo::byValues($variant);
            $price = $combo->price;
        }

        $currency = currentAbsoluteGas()->currency;
        $str = sprintf('%.02f %s / %s', $price, $currency, $product->printableMeasure());

        return response()->json([
            'price' => $str,
        ]);
    }
}
