<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;

use App\Services\SuppliersService;
use App\Exceptions\AuthException;
use App\Exceptions\IllegalArgumentException;

class SuppliersController extends BackedController
{
    public function __construct(SuppliersService $service)
    {
        $this->middleware('auth');

        $this->commonInit([
            'reference_class' => 'App\\Supplier',
            'endpoint' => 'suppliers',
            'service' => $service
        ]);
    }

    public function index()
    {
        try {
            $suppliers = $this->service->list('', true);
            return view('pages.suppliers', ['suppliers' => $suppliers]);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $supplier = $this->service->show($id);
            $user = $request->user();

            if ($user->can('supplier.modify', $supplier) || ($supplier->trashed() && $user->can('supplier.add', $user->gas)))
                return view('supplier.edit', ['supplier' => $supplier]);
            else
                return view('supplier.show', ['supplier' => $supplier]);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
    }

    public function show_ro(Request $request, $id)
    {
        try {
            $supplier = $this->service->show($id);
            return view('supplier.base_show', ['supplier' => $supplier, 'editable' => false]);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
    }

    public function productsDetails(Request $request, $id)
    {
        $supplier = $this->service->show($id);
        if ($request->user()->can('supplier.modify', $supplier))
            return view('supplier.products_details', ['supplier' => $supplier]);
        else
            abort();
    }

    public function productsGrid(Request $request, $id)
    {
        $supplier = $this->service->show($id);
        if ($request->user()->can('supplier.modify', $supplier))
            return view('supplier.products_grid', ['supplier' => $supplier]);
        else
            abort();
    }

    public function catalogue(Request $request, $id, $format = null)
    {
        try {
            return $this->service->catalogue($id, $format, $request->all());
        }
        catch (AuthException $e) {
            abort($e->status());
        }
        catch (IllegalArgumentException $e) {
            return $this->errorResponse($e->getMessage(), $e->getArgument());
        }
    }

    public function invoiceData(Request $request, $id)
    {
        try {
            $supplier = $this->service->show($id);
            return view('supplier.invoicedata', ['supplier' => $supplier]);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
        catch (IllegalArgumentException $e) {
            return $this->errorResponse($e->getMessage(), $e->getArgument());
        }
    }

    public function plainBalance(Request $request, $id)
    {
        try {
            return $this->service->plainBalance($id);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
        catch (IllegalArgumentException $e) {
            return $this->errorResponse($e->getMessage(), $e->getArgument());
        }
    }
}
