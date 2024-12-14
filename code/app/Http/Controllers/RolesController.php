<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\User;
use App\Supplier;
use App\Role;

use App\Services\RolesService;

class RolesController extends BackedController
{
    public function __construct(RolesService $service)
    {
        $this->middleware('auth');

        $this->commonInit([
            'reference_class' => Role::class,
            'service' => $service,
        ]);
    }

    public function index()
    {
        $user = $this->ensureAuth(['gas.permissions' => 'gas']);

        return view('permissions.gas-management', ['gas' => $user->gas]);
    }

    public function show($id)
    {
        $this->ensureAuth(['gas.permissions' => 'gas']);
        $r = Role::findOrFail($id);

        return view('permissions.edit', ['role' => $r]);
    }

    public function formByUser(Request $request, $user_id)
    {
        $this->ensureAuth(['gas.permissions' => 'gas', 'users.admin' => 'gas']);
        $user = User::withTrashed()->find($user_id);

        return view('permissions.user-edit', ['user' => $user]);
    }

    public function formBySupplier(Request $request, $supplier_id)
    {
        $supplier = Supplier::findOrFail($supplier_id);
        $this->ensureAuth(['gas.permissions' => 'gas', 'supplier.modify' => $supplier]);

        return view('permissions.supplier-edit', ['supplier' => $supplier]);
    }

    public function tableByUser(Request $request, $user_id)
    {
        $this->ensureAuth(['gas.permissions' => 'gas', 'users.admin' => 'gas']);
        $user = User::withTrashed()->find($user_id);

        return view('commons.permissionsviewer', ['object' => $user, 'editable' => true]);
    }

    public function tableBySupplier(Request $request, $supplier_id)
    {
        $supplier = Supplier::findOrFail($supplier_id);
        $this->ensureAuth(['gas.permissions' => 'gas', 'supplier.modify' => $supplier]);

        return view('commons.permissionseditor', ['object' => $supplier, 'editable' => true]);
    }

    private function getTargetApplication($request)
    {
        $target_id = $request->input('target_id');
        $target_class = $request->input('target_class');

        if ($target_id) {
            if ($target_id == '*') {
                $target = $target_class;
            }
            else {
                $target = $target_class::tFind($target_id, true);
            }
        }
        else {
            $target = null;
        }

        return $target;
    }

    public function attach(Request $request)
    {
        return $this->easyExecute(function () use ($request) {
            $role_id = $request->input('role');
            $user_id = $request->input('user');

            if ($user_id) {
                $target = $this->getTargetApplication($request);
                [$user, $role] = $this->service->attachUser($user_id, $role_id, $target);

                if (is_null($target)) {
                    /*
                        Qui ci capito quando ho assegnato un nuovo ruolo ad un
                        utente, nel pannello delle configurazioni generali, e
                        restituisco il relativo pannello di configurazione per
                        assegnargli dei soggetti
                    */
                    return view('permissions.main_roleuser', ['role' => $role, 'user' => $user]);
                }
            }
            else {
                $action = $request->input('action');
                $this->service->attachAction($role_id, $action);
            }

            return $this->successResponse();
        });
    }

    public function detach(Request $request)
    {
        return $this->easyExecute(function () use ($request) {
            $role_id = $request->input('role');
            $user_id = $request->input('user');

            if ($user_id) {
                $target = $this->getTargetApplication($request);

                return $this->service->detachUser($user_id, $role_id, $target);
            }
            else {
                $action = $request->input('action');
                $this->service->detachAction($role_id, $action);
            }

            return $this->successResponse();
        });
    }
}
