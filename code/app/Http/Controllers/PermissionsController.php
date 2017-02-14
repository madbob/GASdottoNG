<?php

namespace App\Http\Controllers;

use App\Exceptions\AuthException;
use App\Services\PermissionsService;
use DB;
use Illuminate\Http\Request;

class PermissionsController extends Controller
{

    protected $permissionsService;

    public function __construct(PermissionsService $permissionsService)
    {
        $this->permissionsService = $permissionsService;
    }

    public function getRead(Request $request)
    {
        try {
            $subject_id = $request->input('subject_id');
            $rule_id = $request->input('rule_id');

            $permissions = $this->permissionsService->showForSubject($subject_id, $rule_id);

            return $this->successResponse($permissions);
        } catch (AuthException $e) {
            abort($e->status());
        }
    }

    public function postAdd(Request $request)
    {
        try {
            $user_id = $request->input('user_id');
            $subject_id = $request->input('subject_id');
            $rule_id = $request->input('rule_id');
            $behaviour = $request->input('behaviour');

            $this->permissionsService->add($user_id, $subject_id, $rule_id, $behaviour);

            return $this->successResponse();
        } catch (AuthException $e) {
            abort($e->status());
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function postRemove(Request $request)
    {
        try {
            $user_id = $request->input('user_id');
            $subject_id = $request->input('subject_id');
            $rule_id = $request->input('rule_id');
            $behaviour = $request->input('behaviour');

            $this->permissionsService->remove($user_id, $subject_id, $rule_id, $behaviour);

            return $this->successResponse();
        } catch (AuthException $e) {
            abort($e->status());
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function postChange(Request $request)
    {
        try {
            $user_id = $request->input('user_id');
            $subject_id = $request->input('subject_id');
            $rule_id = $request->input('rule_id');
            $behaviour = $request->input('behaviour');

            $this->permissionsService->change($user_id, $subject_id, $rule_id, $behaviour);

            return $this->successResponse();
        } catch (AuthException $e) {
            abort($e->status());
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
