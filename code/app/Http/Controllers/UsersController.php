<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\User;
use App\Aggregate;

use App\Services\UsersService;
use App\Formatters\User as UserFormatter;
use App\Exceptions\AuthException;

class UsersController extends BackedController
{
    public function __construct(UsersService $service)
    {
        $this->commonInit([
            'reference_class' => User::class,
            'service' => $service,
        ]);
    }

    public function index(Request $request)
    {
        return $this->easyExecute(function () use ($request) {
            $user = $request->user();
            $users = $this->service->list('', $user->can('users.admin', $user->gas));
            $users->loadMissing(['fee', 'roles']);

            return view('pages.users', ['users' => $users]);
        });
    }

    /*
        Il middleware InactiveUser forza un redirect su questa rotta quando
        l'utente non è ancora stato approvato
    */
    public function blocked(Request $request)
    {
        if (!$request->user()->pending) {
            return redirect()->route('dashboard');
        }
        else {
            return view('user.blocked');
        }
    }

    public function revisioned(Request $request, $id)
    {
        return $this->easyExecute(function () use ($id, $request) {
            $status = $request->input('action');
            $this->service->revisioned($id, $status == 'approve');

            return $this->successResponse(['action' => $status]);
        });
    }

    public function promote(Request $request, $id)
    {
        return $this->easyExecute(function () use ($id, $request) {
            $subject = $this->service->promoteFriend($request->all(), $id);

            return $this->commonSuccessResponse($subject);
        });
    }

    public function reassign(Request $request, $id)
    {
        return $this->easyExecute(function () use ($id, $request) {
            $new_parent = $request->input('parent_id');
            $this->service->reassignFriend($id, $new_parent);

            return $this->successResponse();
        });
    }

    public function search(Request $request)
    {
        return $this->easyExecute(function () use ($request) {
            $term = $request->input('term');
            $users = $this->service->list($term);
            $users = $this->toJQueryAutocompletionFormat($users);

            return json_encode($users);
        });
    }

    public function export(Request $request)
    {
        $user = $request->user();
        if ($user->can('users.admin', $user->gas) === false) {
            abort(503);
        }

        $fields = $request->input('fields', []);
        $headers = UserFormatter::getHeaders($fields);
        $users = $this->service->list('', true);

        if ($request->input('exportables') == 'selected') {
            $selected = $this->collectedFilteredUsers($request);
            $users = $users->filter(fn ($u) => in_array($u->id, $selected));
        }

        return output_csv(_i('utenti.csv'), $headers, $users, function ($user) use ($fields) {
            return UserFormatter::format($user, $fields);
        });
    }

    private function getOrders($user_id, $supplier_id, $start, $end)
    {
        return Aggregate::whereHas('orders', function ($query) use ($user_id, $supplier_id, $start, $end) {
            $query->whereHas('bookings', function ($query) use ($user_id) {
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
        return $this->easyExecute(function () use ($request) {
            $id = $request->user()->id;
            $active_tab = $request->input('tab');
            $user = $this->service->show($id);

            return view('pages.profile', ['user' => $user, 'active_tab' => $active_tab]);
        });
    }

    public function searchOrders(Request $request, $id)
    {
        $supplier_id = $request->input('supplier_id');
        $start = decodeDate($request->input('startdate'));
        $end = decodeDate($request->input('enddate'));
        $orders = $this->getOrders($id, $supplier_id, $start, $end);

        return view('commons.orderslist', ['orders' => $orders]);
    }

    public function show($id)
    {
        return $this->easyExecute(function () use ($id) {
            $user = $this->service->show($id);

            return view('user.edit', ['user' => $user]);
        });
    }

    public function show_ro($id)
    {
        return $this->easyExecute(function () use ($id) {
            $user = $this->service->show($id);

            return view('user.edit', ['user' => $user, 'read_only' => true]);
        });
    }

    public function picture($id)
    {
        return $this->easyExecute(function () use ($id) {
            return $this->service->picture($id);
        });
    }

    private function testInternalFunctionsAccess($requester, $target, $type)
    {
        $admin_editable = $requester->can('users.admin', $target->gas);
        $access = ($admin_editable || $requester->id == $target->id || $target->parent_id == $requester->id);

        if ($access === false) {
            if ($type == 'accounting') {
                $access = $requester->can('movements.admin', $target->gas) || $requester->can('movements.view', $target->gas);
            }
            else if ($type == 'friends') {
                $access = $target->can('users.subusers', $target->gas);
            }
        }

        if ($access === false) {
            throw new AuthException(403);
        }
    }

    public function bookings(Request $request, $id)
    {
        return $this->easyExecute(function () use ($request, $id) {
            $user = $this->service->show($id);
            $this->testInternalFunctionsAccess($request->user(), $user, 'bookings');
            $booked_orders = $this->getOrders($id, 0, date('Y-m-d', strtotime('-1 months')), '2100-01-01');

            return view('user.bookings', ['user' => $user, 'booked_orders' => $booked_orders]);
        });
    }

    public function statistics(Request $request, $id)
    {
        return $this->easyExecute(function () use ($request, $id) {
            $user = $this->service->show($id);
            $this->testInternalFunctionsAccess($request->user(), $user, 'accounting');

            return view('commons.statspage', ['target' => $user]);
        });
    }

    public function accounting(Request $request, $id)
    {
        return $this->easyExecute(function () use ($request, $id) {
            $user = $this->service->show($id);
            $this->testInternalFunctionsAccess($request->user(), $user, 'accounting');

            return view('user.accounting', ['user' => $user]);
        });
    }

    public function friends(Request $request, $id)
    {
        return $this->easyExecute(function () use ($request, $id) {
            $user = $this->service->show($id);
            $this->testInternalFunctionsAccess($request->user(), $user, 'friends');

            return view('user.friends', ['user' => $user]);
        });
    }

    private function toJQueryAutocompletionFormat($users)
    {
        $ret = [];
        foreach ($users as $user) {
            $fullname = $user->printableName();
            $u = (object) [
                'id' => $user->id,
                'label' => $fullname,
                'value' => $fullname,
            ];
            $ret[] = $u;
        }

        return $ret;
    }

    /*
        Per ottenere il modale dello "Stato Quote"
    */
    public function fees(Request $request)
    {
        return $this->easyExecute(function () {
            $this->ensureAuth(['users.admin' => 'gas', 'users.movements' => 'gas']);
            $users = $this->service->list('', true);
            $users->loadMissing(['fee', 'fee.related', 'gas', 'latestFee', 'latestFee.related']);

            return view('user.fees', ['users' => $users]);
        });
    }

    /*
        Per ricaricare una singola riga nel modale "Stato Quote", solitamente se
        un movimento di pagamento quota viene eliminato.
        Ogni riga della tabella riporta questo URL come indirizzo per il
        ricaricamento dell'area (funzione reload-portion)
    */
    public function feeRow(Request $request, $id)
    {
        return $this->easyExecute(function () use ($id) {
            $this->ensureAuth(['users.admin' => 'gas', 'users.movements' => 'gas']);
            $user = User::findOrFail($id);

            return view('user.partials.fee_row', ['user' => $user]);
        });
    }

    public function feesSave(Request $request)
    {
        $user = $request->user();

        if ($user->can('users.admin') || $user->can('users.movements')) {
            $users = $request->input('user_id');

            foreach ($users as $user_id) {
                $user = User::tFind($user_id);
                $user->setStatus($request->input('status' . $user_id), $request->input('deleted_at' . $user_id), $request->input('suspended_at' . $user_id));
                $user->save();
            }

            return redirect()->route('users.index');
        }
        else {
            abort(401);
        }
    }

    public function notifications(Request $request, $id)
    {
        return $this->easyExecute(function () use ($request, $id) {
            $this->service->notifications($id, $request->input('suppliers'));

            return $this->successResponse();
        });
    }

    public function changePassword(Request $request)
    {
        if (!$request->user()->enforce_password_change) {
            return redirect()->route('dashboard');
        }

        return view('user.change_password');
    }

    public function destroy($id)
    {
        return $this->easyExecute(function () use ($id) {
            $myself = false;
            if (request()->user()->id == $id) {
                $myself = true;
            }

            $subject = $this->service->destroy($id);

            if ($myself) {
                return redirect()->route('dashboard');
            }

            return $this->commonSuccessResponse($subject);
        });
    }
}
