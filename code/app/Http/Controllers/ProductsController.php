<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BackedController;

use DB;
use Auth;
use Theme;

use App\Services\ProductsService;
use App\Order;
use App\Product;

class ProductsController extends BackedController
{
    public function __construct(ProductsService $service)
    {
        $this->middleware('auth');
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
                return Theme::view('product.edit', ['product' => $product]);
            }
            else {
                return Theme::view('product.show', ['product' => $product]);
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
            return Theme::view('booking.quantityselectrow', ['product' => $product, 'order' => $order, 'populate' => false, 'while_shipping' => true]);
        }
    }

    public function show_ro(Request $request, $id)
    {
        $product = $this->service->show($id);
        return Theme::view('product.show', ['product' => $product]);
    }

    public function store(Request $request)
    {
        try {
            $duplicate = $request->input('duplicate_id', null);
            if ($duplicate) {
                $product = $this->service->duplicate($duplicate);
            }
            else {
                $product = $this->service->store($request->all());
            }

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
        foreach($product_ids as $id) {
            $product = $this->service->show($id);
            $data['name'] = $request->input($id . '-name', $product->name);
            $data['price'] = $request->input($id . '-price', $product->price);
            $data['transport'] = $request->input($id . '-transport', $product->transport);
            $data['measure_id'] = $request->input($id . '-measure_id', $product->measure_id);
            $data['active'] = $request->has($id . '-active');
            $this->service->update($id, $data);
        }

        return $this->successResponse();
    }
}
