<?php

namespace app\Http\Controllers\REST;

use App\Exceptions\AuthException;
use App\Http\Controllers\Controller;
use App\Services\PermissionsService;

class PermissionsController extends Controller
{

    protected $permissionsService;

    public function __construct(PermissionsService $permissionsService)
    {
        $this->permissionsService = $permissionsService;
    }

    public function showForSubject($subject_id, $rule_id)
    {
        try {
            $permissions = $this->permissionsService->showForSubject($subject_id, $rule_id);

            return response()->json(['permissions' => $permissions], 200);
        } catch (AuthException $e) {
            return response(null, $e->status());
        }
    }


}