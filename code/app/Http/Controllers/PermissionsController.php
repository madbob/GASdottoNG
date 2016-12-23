<?php

namespace app\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use App\Permission;
use App\User;

class PermissionsController extends Controller
{
    public function getRead(Request $request)
    {
        DB::beginTransaction();

        $subject_id = $request->input('subject_id');
        $rule_id = $request->input('rule_id');

        $class = Permission::classByRule($rule_id);
        if ($class == null) {
            return $this->errorResponse('Regola non trovata');
        }

        $subject = $class::findOrFail($subject_id);
        if ($subject->permissionsCanBeModified() == false) {
            return $this->errorResponse('Non autorizzato');
        }

        $ret = $subject->whoCanComplex($rule_id);

        return $this->successResponse($ret);
    }

    public function postAdd(Request $request)
    {
        DB::beginTransaction();

        $user_id = $request->input('user_id');
        $subject_id = $request->input('subject_id');
        $rule_id = $request->input('rule_id');
        $behaviour = $request->input('behaviour');

        $class = Permission::classByRule($rule_id);
        if ($class == null) {
            return $this->errorResponse('Regola non trovata');
        }

        $subject = $class::findOrFail($subject_id);
        if ($subject->permissionsCanBeModified() == false) {
            return $this->errorResponse('Non autorizzato');
        }

        switch ($behaviour) {
            case 'all':
                /*
                    Se tutti gli utenti sono autorizzati per
                    la regola, non può esistere il caso in
                    cui si intervenga su uno solo.
                    Cfr. postChange()
                */
                break;

            case 'selected':
                $subject->userPermit($rule_id, $user_id);
                break;

            case 'except':
                $subject->userRevoke($rule_id, $user_id);
                break;

            default:
                return $this->errorResponse('Comportamento non ammesso');
        }

        return $this->successResponse();
    }

    public function postRemove(Request $request)
    {
        DB::beginTransaction();

        $user_id = $request->input('user_id');
        $subject_id = $request->input('subject_id');
        $rule_id = $request->input('rule_id');
        $behaviour = $request->input('behaviour');

        $class = Permission::classByRule($rule_id);
        if ($class == null) {
            return $this->errorResponse('Regola non trovata');
        }

        $subject = $class::findOrFail($subject_id);
        if ($subject->permissionsCanBeModified() == false) {
            return $this->errorResponse('Non autorizzato');
        }

        switch ($behaviour) {
            case 'all':
                /*
                    Se tutti gli utenti sono autorizzati per
                    la regola, non può esistere il caso in
                    cui si intervenga su uno solo.
                    Cfr. postChange()
                */
                break;

            case 'selected':
                $subject->userRevoke($rule_id, $user_id);
                break;

            case 'except':
                $subject->userPermit($rule_id, $user_id);
                break;

            default:
                return $this->errorResponse('Comportamento non ammesso');
        }

        return $this->successResponse();
    }

    private function swapAuthorizations($subject, $rule)
    {
        $current_users = $subject->whoCan($rule);
        $new_users = User::whereNotIn('id', $current_users)->get();

        foreach ($new_users as $user) {
            $subject->userPermit($rule, $user);
        }

        foreach ($current_users as $user) {
            $subject->userRevoke($rule, $user);
        }
    }

    public function postChange(Request $request)
    {
        DB::beginTransaction();

        $subject_id = $request->input('subject_id');
        $rule_id = $request->input('rule_id');
        $behaviour = $request->input('behaviour');

        $class = Permission::classByRule($rule_id);
        if ($class == null) {
            return $this->errorResponse('Regola non trovata');
        }

        $subject = $class::findOrFail($subject_id);
        if ($subject->permissionsCanBeModified() == false) {
            return $this->errorResponse('Non autorizzato');
        }

        switch ($behaviour) {
            case 'all':
                $subject->userPermit($rule_id, '*');
                break;

            case 'selected':
            case 'except':
                $this->swapAuthorizations($subject, $rule_id);
                break;

            default:
                return $this->errorResponse('Comportamento non ammesso');
        }

        return $this->successResponse();
    }
}
