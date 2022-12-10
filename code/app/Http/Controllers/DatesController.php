<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;

use App\Date;
use App\Supplier;

use App\Services\DatesService;

class DatesController extends BackedController
{
    public function __construct(DatesService $service)
    {
        $this->middleware('auth');

        $this->commonInit([
            'reference_class' => 'App\\Date',
            'service' => $service
        ]);
    }

    public function index()
    {
        return $this->easyExecute(function() {
            $dates = $this->service->list(null, true, ['confirmed', 'temp']);
            return view('dates.table', ['dates' => $dates]);
        });
    }

    public function query(Request $request)
    {
        return $this->easyExecute(function() use ($request) {
            $supplier_id = $request->input('supplier_id');
            $supplier = Supplier::find($supplier_id);
            if ($supplier == null)
                abort(404);

            $dates = $supplier->calendarDates;
            return view('dates.list', ['dates' => $dates]);
        });
    }

    public function show(Request $request, $id)
    {
        return $this->easyExecute(function() use ($request, $id) {
            $date = $this->service->show($id);
            $user = $request->user();

            if ($user->can('notifications.admin', $user->gas))
                return view('dates.edit', ['date' => $date]);
            else
                return view('dates.show', ['date' => $date]);
        });
    }

    public function orders()
    {
        return $this->easyExecute(function() {
            $dates = $this->service->list(null, true, ['order']);
            return view('dates.orders', ['dates' => $dates]);
        });
    }

    public function updateOrders(Request $request)
    {
        return $this->easyExecute(function() use ($request) {
            $this->service->updateOrders($request->except('_method', '_token'));
            return $this->commonSuccessResponse(null);
        });
    }
}
