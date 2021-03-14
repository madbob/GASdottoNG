<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;

use App\Date;
use App\Supplier;

use App\Services\DatesService;
use App\Exceptions\AuthException;
use App\Exceptions\IllegalArgumentException;

class DatesController extends BackedController
{
    public function __construct(DatesService $service)
    {
        $this->middleware('auth');

        $this->commonInit([
            'reference_class' => 'App\\Date',
            'endpoint' => 'dates',
            'service' => $service
        ]);
    }

    public function index()
    {
        try {
            $dates = $this->service->list(null, true, ['confirmed', 'temp']);
            return view('dates.table', ['dates' => $dates]);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
    }

    public function query(Request $request)
    {
        try {
            $supplier_id = $request->input('supplier_id');
            $supplier = Supplier::find($supplier_id);
            if ($supplier == null)
                abort(404);

            $dates = $supplier->calendarDates;
            return view('dates.list', ['dates' => $dates]);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $date = $this->service->show($id);
            $user = $request->user();

            if ($user->can('notifications.admin', $user->gas))
                return view('dates.edit', ['date' => $date]);
            else
                return view('dates.show', ['date' => $date]);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
    }

    public function orders()
    {
        try {
            $dates = $this->service->list(null, true, ['order']);
            return view('dates.orders', ['dates' => $dates]);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
    }

    public function updateOrders(Request $request)
    {
        try {
            $this->service->updateOrders($request->except('_method', '_token'));
            return $this->commonSuccessResponse(null);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
        catch (IllegalArgumentException $e) {
            return $this->errorResponse($e->getMessage(), $e->getArgument());
        }
    }
}
