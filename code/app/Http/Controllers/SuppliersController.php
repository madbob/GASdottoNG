<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\SuppliersService;
use App\Http\Controllers\Concerns\RoutesPictures;

class SuppliersController extends BackedController
{
    use RoutesPictures;

    public function __construct(SuppliersService $service)
    {
        $this->middleware('auth');

        $this->commonInit([
            'reference_class' => 'App\\Supplier',
            'service' => $service,
        ]);
    }

    public function index()
    {
        return $this->easyExecute(function () {
            $suppliers = $this->service->list('', true);

            return view('pages.suppliers', ['suppliers' => $suppliers]);
        });
    }

    public function show(Request $request, $id)
    {
        return $this->easyExecute(function () use ($request, $id) {
            $supplier = $this->service->show($id);
            $user = $request->user();

            if ($user->can('supplier.modify', $supplier) || ($supplier->trashed() && $user->can('supplier.add', $user->gas))) {
                return view('supplier.edit', ['supplier' => $supplier]);
            }
            else {
                return view('supplier.show', ['supplier' => $supplier]);
            }
        });
    }

    public function show_ro($id)
    {
        return $this->easyExecute(function () use ($id) {
            $supplier = $this->service->show($id);

            return view('supplier.base_show', [
                'supplier' => $supplier,
                'editable' => false,
                'selfview' => true,
            ]);
        });
    }

    public function productsDetails(Request $request, $id)
    {
        $supplier = $this->service->show($id);
        if ($request->user()->can('supplier.modify', $supplier)) {
            return view('supplier.products_details', ['supplier' => $supplier]);
        }
        else {
            abort(401);
        }
    }

    public function productsGrid(Request $request, $id)
    {
        $supplier = $this->service->show($id);
        if ($request->user()->can('supplier.modify', $supplier)) {
            return view('supplier.products_grid', ['supplier' => $supplier]);
        }
        else {
            abort(401);
        }
    }

    public function invoiceData($id)
    {
        return $this->easyExecute(function () use ($id) {
            $supplier = $this->service->show($id);

            return view('supplier.invoicedata', ['supplier' => $supplier]);
        });
    }
}
