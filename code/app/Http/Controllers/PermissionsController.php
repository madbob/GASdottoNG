<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Auth;
use Theme;
use DB;

use App\Permission;
use App\User;

class PermissionsController extends Controller
{
        public function getRead(Request $request)
        {
                DB::beginTransaction();

                $user = Auth::user();
		if ($user->gas->userCan('gas.permissions') == false)
			return $this->errorResponse('Non autorizzato');

                $subject_id = $request->input('subject_id');
                $rule_id = $request->input('rule_id');

                $class = Permission::classByRule($rule_id);
                if ($class == null)
                        return $this->errorResponse('Regola non trovata');

                $subject = $class::findOrFail($subject_id);
                $users = $subject->whoCan($rule_id);

                $current_users = User::count();
                $ret_users = [];
                $behaviour = '';

                if (array_key_exists('*', $users)) {
                        $behaviour = 'all';
                }
                else if (count($users) > ($current_users / 2)) {
                        $excluded_users = User::whereNotIn('id', $users)->orderBy('name', 'asc')->get();
                        foreach($excluded_users as $eu) {
                                $ret_users[] = (object) [
                                        'id' => $eu->id,
                                        'name' => $eu->printableName(),
                                ];
                        }

                        $behaviour = 'except';
                }
                else {
                        $included_users = User::whereIn('id', $users)->orderBy('name', 'asc')->get();
                        foreach($included_users as $iu) {
                                $ret_users[] = (object) [
                                        'id' => $iu->id,
                                        'name' => $iu->printableName(),
                                ];
                        }

                        $behaviour = 'selected';
                }

                return $this->successResponse([
			'behaviour' => $behaviour,
			'users' => $ret_users
		]);
        }

        public function postAdd(Request $request)
        {
                DB::beginTransaction();

                $user = Auth::user();
		if ($user->gas->userCan('gas.permissions') == false)
			return $this->errorResponse('Non autorizzato');

                $user_id = $request->input('user_id');
                $subject_id = $request->input('subject_id');
                $rule_id = $request->input('rule_id');
                $behaviour = $request->input('behaviour');

                $class = Permission::classByRule($rule_id);
                if ($class == null)
                        return $this->errorResponse('Regola non trovata');

                $subject = $class::findOrFail($subject_id);

                switch($behaviour) {
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

                $user = Auth::user();
		if ($user->gas->userCan('gas.permissions') == false)
			return $this->errorResponse('Non autorizzato');

                $user_id = $request->input('user_id');
                $subject_id = $request->input('subject_id');
                $rule_id = $request->input('rule_id');
                $behaviour = $request->input('behaviour');

                $class = Permission::classByRule($rule_id);
                if ($class == null)
                        return $this->errorResponse('Regola non trovata');

                $subject = $class::findOrFail($subject_id);

                switch($behaviour) {
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

                foreach($new_users as $user)
                        $subject->userPermit($rule, $user);

                foreach($current_users as $user)
                        $subject->userRevoke($rule, $user);
        }

        public function postChange(Request $request)
        {
                DB::beginTransaction();

                $user = Auth::user();
		if ($user->gas->userCan('gas.permissions') == false)
			return $this->errorResponse('Non autorizzato');

                $subject_id = $request->input('subject_id');
                $rule_id = $request->input('rule_id');
                $behaviour = $request->input('behaviour');

                $class = Permission::classByRule($rule_id);
                if ($class == null)
                        return $this->errorResponse('Regola non trovata');

                $subject = $class::findOrFail($subject_id);

                switch($behaviour) {
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
