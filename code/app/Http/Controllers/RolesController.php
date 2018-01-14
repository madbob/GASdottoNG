<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Auth;
use DB;
use Theme;

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

        return Theme::view('commons.loadablelist', [
            'identifier' => 'role-list',
            'items' => Role::sortedByHierarchy()
        ]);
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
        $r->always = $request->has('always');
        $r->parent_id = $request->input('parent_id');
        $r->actions = join(',', $request->input('actions', []));
        $r->save();

        return $this->successResponse([
            'id' => $r->id,
            'name' => $r->name,
            'header' => $r->printableHeader(),
            'url' => url('roles/' . $r->id),
        ]);
    }

    public function show($id)
    {
        $user = Auth::user();
        if ($user->can('gas.permissions', $user->gas) == false) {
            abort(503);
        }

        $r = Role::findOrFail($id);
        return Theme::view('permissions.edit', ['role' => $r]);
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
        $r->always = $request->has('always');
        $r->parent_id = $request->input('parent_id');
        $r->save();

        return $this->successResponse([
            'id' => $r->id,
            'header' => $r->printableHeader(),
            'url' => url('roles/' . $r->id),
        ]);
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
        return Theme::view('permissions.user-edit', ['user' => $user]);
    }

    public function formBySupplier(Request $request, $supplier_id)
    {
        $user = Auth::user();
        $supplier = Supplier::find($supplier_id);

        if ($user->can('gas.permissions', $user->gas) == false && $user->can('supplier.modify', $supplier) == false) {
            abort(503);
        }

        return Theme::view('permissions.supplier-edit', ['supplier' => $supplier]);
    }

    public function attach(Request $request)
    {
        DB::beginTransaction();

        $user = Auth::user();
        if ($user->can('gas.permissions', $user->gas) == false && $user->can('users.admin', $user->gas) == false) {
            return $this->errorResponse(_i('Non autorizzato'));
        }

        $role_id = $request->input('role');
        $r = Role::findOrFail($role_id);

        if ($request->has('user')) {
            $user_id = $request->input('user');
            $u = User::findOrFail($user_id);

            if ($request->has('target_id')) {
                $target_id = $request->input('target_id');
                $target_class = $request->input('target_class');

                if ($target_id == '*')
                    $target = $target_class;
                else
                    $target = $target_class::findOrFail($target_id);

                $u->addRole($r, $target);
                return $this->successResponse();
            }
            else {
                $u->addRole($r, null);

                DB::commit();
                return Theme::view('permissions.main_roleuser', ['role' => $r, 'user' => $u]);
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
        if ($user->can('gas.permissions', $user->gas) == false && $user->can('users.admin', $user->gas) == false) {
            return $this->errorResponse(_i('Non autorizzato'));
        }

        $role_id = $request->input('role');
        $r = Role::findOrFail($role_id);

        if ($request->has('user')) {
            if ($request->has('target_id')) {
                $target_id = $request->input('target_id');
                $target_class = $request->input('target_class');

                if ($target_id == '*')
                    $target = $target_class;
                else
                    $target = $target_class::findOrFail($target_id);
            }
            else {
                $target = null;
            }

            $user_id = $request->input('user');
            $u = User::findOrFail($user_id);
            $u->removeRole($r, $target);
        }
        else {
            $action = $request->input('action');
            $r->disableAction($action);
        }

        return $this->successResponse();
    }
}
