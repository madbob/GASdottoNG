<?php

namespace app\Http\Controllers;

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
    }

    private function basicReadFromRequest(&$obj, $request)
    {
        $obj->name = $request->input('name');
        $obj->description = $request->input('description');
        $obj->price = $request->input('price');
        $obj->transport = $request->input('transport');
        $obj->discount = normalizePercentage($request->input('discount'));
        $obj->category_id = $request->input('category_id');
        $obj->measure_id = $request->input('measure_id');
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        $supplier = Supplier::findOrFail($request->input('supplier_id'));
        if ($supplier->userCan('supplier.modify') == false) {
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
        $format = $request->input('format', 'html');
        $p = Product::with('variants')->with('variants.values')->findOrFail($id);

        if ($format == 'html') {
            if ($p->supplier->userCan('supplier.modify')) {
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

            return Theme::view('booking.quantityselectrow', ['product' => $p, 'order' => $order, 'populate' => false]);
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        $p = Product::findOrFail($id);
        if ($p->supplier->userCan('supplier.modify') == false) {
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

        if ($p->measure->discrete) {
            $p->portion_quantity = 0;
            $p->variable = false;
        } else {
            $p->portion_quantity = $request->input('portion_quantity');
            $p->variable = $request->has('variable') ? true : false;
        }

        $p->save();

        return $this->successResponse([
            'id' => $p->id,
            'header' => $p->printableHeader(),
            'url' => url('products/'.$p->id),
        ]);
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        $p = Product::findOrFail($id);

        if ($p->supplier->userCan('supplier.modify') == false) {
            return $this->errorResponse('Non autorizzato');
        }

        $p->delete();

        return $this->successResponse();
    }
}
