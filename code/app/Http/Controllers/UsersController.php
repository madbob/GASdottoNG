<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;

use App\User;
use App\Aggregate;

use App\Services\UsersService;
use App\Exceptions\AuthException;
use App\Exceptions\IllegalArgumentException;

class UsersController extends BackedController
{
    public function __construct(UsersService $service)
    {
        $this->middleware('auth');

        $this->commonInit([
            'reference_class' => 'App\\User',
            'endpoint' => 'users',
            'service' => $service
        ]);
    }

    public function index()
    {
        try {
            $user = Auth::user();
            $users = $this->service->list('', $user->can('users.admin', $user->gas));
            return view('pages.users', ['users' => $users]);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
    }

    public function search(Request $request)
    {
        $term = $request->input('term');

        try {
            $users = $this->service->list($term);
            $users = $this->toJQueryAutocompletionFormat($users);
            return json_encode($users);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
    }

    public function export(Request $request)
    {
        $user = Auth::user();
        if ($user->can('users.admin', $user->gas) == false) {
            abort(503);
        }

        $fields = $request->input('fields', []);
        $printable = $request->input('printable', []);
        $formattable = User::formattableColumns();
        $headers = [];
        foreach($fields as $f) {
            $headers[] = $formattable[$f]->name;
        }

        $users = $this->service->list('', true, $printable);

        return output_csv(_i('utenti.csv'), $headers, $users, function($user) use ($fields) {
            return $user->formattedFields($fields);
        });
    }

    private function getOrders($user_id, $supplier_id, $start, $end)
    {
        return Aggregate::whereHas('orders', function($query) use ($user_id, $supplier_id, $start, $end) {
            $query->whereHas('bookings', function($query) use ($user_id) {
                $query->where('user_id', $user_id);
            });

            if ($start) {
                $query->where('start', '>=', $start);
            }

            if ($end) {
                $query->where('end', '<=', $end);
            }

            if ($supplier_id != '0') {
                $query->where('supplier_id', $supplier_id);
            }
        })->with('orders')->get();
    }

    public function profile(Request $request)
    {
        try {
            $id = Auth::user()->id;
            $active_tab = $request->input('tab');
            $user = $this->service->show($id);
            $booked_orders = $this->getOrders($id, 0, date('Y-m-d', strtotime('-1 months')), '2100-01-01');
            return view('pages.profile', ['user' => $user, 'active_tab' => $active_tab, 'booked_orders' => $booked_orders]);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
    }

    public function searchOrders(Request $request, $id)
    {
        $supplier_id = $request->input('supplier_id');
        $start = decodeDate($request->input('startdate'));
        $end = decodeDate($request->input('enddate'));
        $orders = $this->getOrders($id, $supplier_id, $start, $end);
        return view('commons.orderslist', ['orders' => $orders]);
    }

    public function show(Request $request, $id)
    {
        try {
            $user = $this->service->show($id);
            $booked_orders = $this->getOrders($id, 0, date('Y-m-d', strtotime('-1 months')), '2100-01-01');
            return view('user.edit', ['user' => $user, 'booked_orders' => $booked_orders]);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
    }

    public function show_ro(Request $request, $id)
    {
        try {
            $user = $this->service->show($id);
            return view('user.edit', ['user' => $user, 'read_only' => true]);
        }
        catch (AuthException $e) {
            abort($e->status());
        }
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

    private function toJQueryAutocompletionFormat($users)
    {
        $ret = [];
        foreach ($users as $user) {
            $fullname = $user->printableName();
            $u = (object)array(
                'id' => $user->id,
                'label' => $fullname,
                'value' => $fullname
            );
            $ret[] = $u;
        }
        return $ret;
    }

    public function notifications(Request $request, $id)
    {
        try {
            $this->service->notifications($id, $request->input('suppliers'));
            return $this->successResponse();
        }
        catch (AuthException $e) {
            abort($e->status());
        }
    }

    public function changePassword(Request $request)
    {
        if ($request->user()->enforce_password_change == false)
            return redirect()->route('dashboard');
        return view('user.change_password');
    }
}
