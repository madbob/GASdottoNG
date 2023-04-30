<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BackedController;

use DB;
use Auth;

use App\Services\ProductsService;

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
        return $this->easyExecute(function() use ($request) {
            $product = $this->service->store($request->all());
            return $this->commonSuccessResponse($product);
        });
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
            $data['max_available'] = $request->input($id . '-max_available', $product->max_available);
            $data['active'] = $request->has($id . '-active');
			$data['variable'] = $product->variable;
            $data['sorting'] = $index;
            $this->service->update($id, $data);
        }

        return $this->successResponse();
    }

    public function picture($id)
    {
        return $this->easyExecute(function() use ($id) {
            return $this->service->picture($id);
        });
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

        $currency = defaultCurrency()->symbol;
        $str = sprintf('%.02f %s / %s', $price, $currency, $product->printableMeasure());

        return response()->json([
            'price' => $str,
        ]);
    }

    private function unalignedPrices($product)
    {
        $to_change = [];
        $orders = $product->supplier->active_orders;

        foreach($orders as $order) {
            $existing_product = $order->products()->where('product_id', $product->id)->first();

            if ($existing_product) {
                if ($product->comparePrices($existing_product) == false) {
                    $to_change[] = $order;
                }
            }
        }

        return $to_change;
    }

    public function postFeedback(Request $request, $id)
    {
        $ret = [];
        $product = Product::findOrFail($id);

        $to_change = $this->unalignedPrices($product);
        if (empty($to_change) == false) {
            $ret[] = route('products.askupdateprices', $product->id);
        }

        return response()->json($ret);
    }

    public function askUpdatePrices(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $to_change = $this->unalignedPrices($product);

        return view('product.updateorders', [
            'product' => $product,
            'orders' => $to_change,
        ]);
    }

    public function updatePrices(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $orders = $request->input('orders', []);

        foreach($orders as $order) {
            $order = Order::find($order);
            $order->attachProduct($product);
        }

        return $this->successResponse();
    }
}
