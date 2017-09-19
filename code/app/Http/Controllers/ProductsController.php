<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Auth;
use Theme;
use App\Supplier;
use App\Order;
use App\Product;

class ProductsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->commonInit([
            'reference_class' => 'App\\Product'
        ]);
    }

    private function basicReadFromRequest(&$obj, $request)
    {
        $obj->name = $request->input('name');
        $obj->description = $request->input('description');
        $obj->price = $request->input('price');
        $obj->transport = $request->input('transport', 0);
        $obj->discount = normalizePercentage($request->input('discount'));
        $obj->category_id = $request->input('category_id');
        $obj->measure_id = $request->input('measure_id');

        $vat_rate = $request->input('vat_rate_id');
        if ($vat_rate != 0)
            $obj->vat_rate_id = $vat_rate;
        else
            $obj->vat_rate_id = null;
    }

    private function enforceMeasure($product, $request)
    {
        if ($product->measure->discrete) {
            $product->portion_quantity = 0;
            $product->variable = false;
        }
        else {
            $product->portion_quantity = $request->input('portion_quantity', 0);
            $product->variable = $request->has('variable') ? true : false;
        }

        return $product;
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        $user = Auth::user();
        $supplier = Supplier::findOrFail($request->input('supplier_id'));

        if ($user->can('supplier.modify', $supplier) == false) {
            return $this->errorResponse('Non autorizzato');
        }

        $p = new Product();
        $p->supplier_id = $supplier->id;
        $p->active = true;
        $this->basicReadFromRequest($p, $request);
        $p->save();

        return $this->successResponse([
            'id' => $p->id,
            'name' => $p->name,
            'header' => $p->printableHeader(),
            'url' => url('products/'.$p->id),
        ]);
    }

    public function show(Request $request, $id)
    {
        $user = Auth::user();

        $format = $request->input('format', 'html');
        $p = Product::with('variants')->with('variants.values')->findOrFail($id);

        if ($format == 'html') {
            if ($user->can('supplier.modify', $p->supplier)) {
                return Theme::view('product.edit', ['product' => $p]);
            } else {
                return Theme::view('product.show', ['product' => $p]);
            }
        } elseif ($format == 'json') {
            $ret = $p->toJson();
            $ret = json_decode($ret);
            $ret->printableMeasure = $p->printableMeasure();

            return json_encode($ret);
        } elseif ($format == 'bookable') {
            $order = Order::find($request->input('order_id'));

            return Theme::view('booking.quantityselectrow', ['product' => $p, 'order' => $order, 'populate' => false, 'while_shipping' => true]);
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        $user = Auth::user();
        $p = Product::findOrFail($id);

        if ($user->can('supplier.modify', $p->supplier) == false) {
            return $this->errorResponse('Non autorizzato');
        }

        $this->basicReadFromRequest($p, $request);
        $p->active = $request->has('active');
        $p->supplier_code = $request->has('supplier_code');
        $p->package_size = $request->input('package_size');
        $p->multiple = $request->input('multiple');
        $p->min_quantity = $request->input('min_quantity');
        $p->max_quantity = $request->input('max_quantity');
        $p->max_available = $request->input('max_available');
        $p = $this->enforceMeasure($p, $request);

        $p->save();

        return $this->successResponse([
            'id' => $p->id,
            'header' => $p->printableHeader(),
            'url' => url('products/'.$p->id),
        ]);
    }

    public function massiveUpdate(Request $request)
    {
        DB::beginTransaction();

        $user = Auth::user();

        $product_ids = $request->input('id', []);
        foreach($product_ids as $id) {
            $product = Product::with('supplier')->findOrFail($id);
            if ($user->can('supplier.modify', $product->supplier) == false)
                continue;

            $product->name = $request->input($id . '-name', $product->name);
            $product->price = $request->input($id . '-price', $product->price);
            $product->transport = $request->input($id . '-transport', $product->transport);
            $product->measure_id = $request->input($id . '-measure_id', $product->measure_id);
            $product = $this->enforceMeasure($product, $request);
            $product->save();
        }

        return $this->successResponse();
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        $user = Auth::user();
        $p = Product::findOrFail($id);

        if ($user->can('supplier.modify', $p->supplier) == false) {
            return $this->errorResponse('Non autorizzato');
        }

        $p->delete();

        return $this->successResponse();
    }
}
