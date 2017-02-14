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
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function add($user_id, $subject_id, $rule_id, $behaviour)
    {
        try {
            $this->permissionsService->add($user_id, $subject_id, $rule_id, $behaviour);

            return response(null, 200);
        } catch (AuthException $e) {
            return response(null, $e->status());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function remove($user_id, $subject_id, $rule_id, $behaviour)
    {
        try {
            $this->permissionsService->remove($user_id, $subject_id, $rule_id, $behaviour);

            return response(null, 200);
        } catch (AuthException $e) {
            return response(null, $e->status());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function change($user_id, $subject_id, $rule_id, $behaviour)
    {
        try {
            $this->permissionsService->change($user_id, $subject_id, $rule_id, $behaviour);

            return response(null, 200);
        } catch (AuthException $e) {
            return response(null, $e->status());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}