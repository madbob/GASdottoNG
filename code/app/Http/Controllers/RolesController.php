<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Auth;
use DB;
use Log;

use App\User;
use App\Supplier;
use App\Role;

class RolesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->commonInit([
            'reference_class' => 'App\\Role'
        ]);
    }

    public function index()
    {
        $user = Auth::user();
        if ($user->can('gas.permissions', $user->gas) == false) {
            return $this->errorResponse(_i('Non autorizzato'));
        }

        return view('permissions.gas-management', ['gas' => $user->gas]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if ($user->can('gas.permissions', $user->gas) == false) {
            return $this->errorResponse(_i('Non autorizzato'));
        }

        DB::beginTransaction();

        $r = new Role();
        $r->name = $request->input('name');
        $r->parent_id = $request->input('parent_id');
        $r->actions = join(',', $request->input('actions', []));
        $r->save();

        return $this->commonSuccessResponse($r);
    }

    public function show($id)
    {
        $user = Auth::user();
        if ($user->can('gas.permissions', $user->gas) == false) {
            abort(503);
        }

        $r = Role::findOrFail($id);
        return view('permissions.edit', ['role' => $r]);
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        $user = Auth::user();
        if ($user->can('gas.permissions', $user->gas) == false) {
            return $this->errorResponse(_i('Non autorizzato'));
        }

        $r = Role::findOrFail($id);
        $r->name = $request->input('name');
        $r->parent_id = $request->input('parent_id');
        $r->save();

        return $this->commonSuccessResponse($r);
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        $user = Auth::user();
        if ($user->can('gas.permissions', $user->gas) == false) {
            return $this->errorResponse(_i('Non autorizzato'));
        }

        $r = Role::findOrFail($id);
        $r->delete();

        return $this->successResponse();
    }

    public function formByUser(Request $request, $user_id)
    {
        $user = Auth::user();
        if ($user->can('gas.permissions', $user->gas) == false && $user->can('users.admin', $user->gas) == false) {
            abort(503);
        }

        $user = User::find($user_id);
        return view('permissions.user-edit', ['user' => $user]);
    }

    public function formBySupplier(Request $request, $supplier_id)
    {
        $user = Auth::user();
        $supplier = Supplier::findOrFail($supplier_id);

        if ($user->can('gas.permissions', $user->gas) == false && $user->can('supplier.modify', $supplier) == false) {
            abort(503);
        }

        return view('permissions.supplier-edit', ['supplier' => $supplier]);
    }

    public function tableByUser(Request $request, $user_id)
    {
        $user = Auth::user();
        if ($user->can('gas.permissions', $user->gas) == false && $user->can('users.admin', $user->gas) == false) {
            abort(503);
        }

        $user = User::find($user_id);
        return view('commons.permissionsviewer', ['object' => $user, 'editable' => true]);
    }

    public function tableBySupplier(Request $request, $supplier_id)
    {
        $user = Auth::user();
        $supplier = Supplier::findOrFail($supplier_id);

        if ($user->can('gas.permissions', $user->gas) == false && $user->can('supplier.modify', $supplier) == false) {
            abort(503);
        }

        return view('commons.permissionseditor', ['object' => $supplier, 'editable' => true]);
    }

    public function attach(Request $request)
    {
        DB::beginTransaction();

        $user = Auth::user();
        $role_id = $request->input('role');

        $managed_roles = $user->managed_roles->search(function($item, $key) use ($role_id) {
            return $item->id == $role_id;
        });

        if ($user->can('gas.permissions', $user->gas) == false && $user->can('users.admin', $user->gas) == false && $managed_roles === false) {
            return $this->errorResponse(_i('Non autorizzato'));
        }

        $r = Role::findOrFail($role_id);

        if ($request->has('user')) {
            $user_id = $request->input('user');
            $u = User::tFind($user_id, true);

            if ($request->has('target_id')) {
                $target_id = $request->input('target_id');
                $target_class = $request->input('target_class');

                if ($target_id == '*')
                    $target = $target_class;
                else
                    $target = $target_class::tFind($target_id, true);

                $u->addRole($r, $target);
                return $this->successResponse();
            }
            else {
                $attached = $u->addRole($r, null);

                foreach($r->getAllClasses() as $target_class) {
                    $available_targets = Role::targetsByClass($target_class);
                    if ($available_targets->count() == 1)
                        $attached->attachApplication($available_targets->get(0));
                }

                DB::commit();
                return view('permissions.main_roleuser', ['role' => $r, 'user' => $u]);
            }
        }
        else {
            $action = $request->input('action', null);
            if ($action != null) {
                $r->enableAction($action);
                return $this->successResponse();
            }
            else {
                return $this->errorResponse(_i('Parametri mancanti'));
            }
        }
    }

    public function detach(Request $request)
    {
        DB::beginTransaction();

        $user = Auth::user();
        $role_id = $request->input('role');

        $managed_roles = $user->managed_roles->search(function($item, $key) use ($role_id) {
            return $item->id == $role_id;
        });

        if ($user->can('gas.permissions', $user->gas) == false && $user->can('users.admin', $user->gas) == false && $managed_roles === false) {
            return $this->errorResponse(_i('Non autorizzato'));
        }

        $r = Role::findOrFail($role_id);

        if ($request->has('user')) {
            if ($request->has('target_id')) {
                $target_id = $request->input('target_id');
                $target_class = $request->input('target_class');

                if ($target_id == '*')
                    $target = $target_class;
                else
                    $target = $target_class::tFind($target_id, true);
            }
            else {
                $target = null;
            }

            $user_id = $request->input('user');
            $u = User::tFind($user_id, true);
            $u->removeRole($r, $target);
        }
        else {
            $action = $request->input('action');
            $r->disableAction($action);
        }

        return $this->successResponse();
    }
}
