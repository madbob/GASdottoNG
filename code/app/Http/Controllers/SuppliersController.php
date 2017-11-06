<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use Theme;

use App\Services\SuppliersService;
use App\Exceptions\AuthException;
use App\Exceptions\IllegalArgumentException;

class SuppliersController extends Controller
{
    public function __construct(SuppliersService $suppliersService)
    {
        $this->middleware('auth');
        $this->suppliersService = $suppliersService;

        $this->commonInit([
            'reference_class' => 'App\\Supplier',
            'endpoint' => 'suppliers'
        ]);
    }

    public function index()
    {
        try {
            $suppliers = $this->suppliersService->list('', true);
            return Theme::view('pages.suppliers', ['suppliers' => $suppliers]);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
    }

    public function store(Request $request)
    {
        try {
            $supplier = $this->suppliersService->store($request->all());
            return $this->commonSuccessResponse($supplier);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
        catch (IllegalArgumentException $e) {
            return $this->errorResponse($e->getMessage(), $e->getArgument());
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $supplier = $this->suppliersService->show($id);

            if ($request->user()->can('supplier.modify', $supplier))
                return Theme::view('supplier.edit', ['supplier' => $supplier]);
            else
                return Theme::view('supplier.show', ['supplier' => $supplier]);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $supplier = $this->suppliersService->update($id, $request->all());
            return $this->commonSuccessResponse($supplier);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
        catch (IllegalArgumentException $e) {
            return $this->errorResponse($e->getMessage(), $e->getArgument());
        }
    }

    public function destroy($id)
    {
        try {
            $this->suppliersService->destroy($id);
            return $this->successResponse();
        }
        catch (AuthException $e) {
            abort($e->status());
        }
    }

    public function catalogue(Request $request, $id, $format)
    {
        try {
            return $this->suppliersService->catalogue($id, $format);
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
            return $this->suppliersService->plainBalance($id);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
        catch (IllegalArgumentException $e) {
            return $this->errorResponse($e->getMessage(), $e->getArgument());
        }
    }
}
