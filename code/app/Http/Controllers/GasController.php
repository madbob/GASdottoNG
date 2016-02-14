<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use DB;
use Theme;

use App\Permission;
use App\Gas;

class GasController extends Controller
{
        public function __construct()
	{
		$this->middleware('auth');
	}

        public function edit($id)
        {
                $gas = Gas::findOrFail($id);
                if ($gas->userCan('gas.config') == false)
                        abort(503);

                $permissions = Permission::allPermissions();
                foreach($permissions as $class => $types) {
                        $all = $class::all();
                        foreach($all as $subject)
                                $permissions_subjects[] = $subject;
                }

                return Theme::view('pages.gas', ['gas' => $gas, 'permissions_subjects' => $permissions_subjects, 'permissions_rules' => $permissions]);
        }

        public function update(Request $request, $id)
        {
                DB::beginTransaction();

                $gas = Gas::findOrFail($id);
                if ($gas->userCan('gas.config') == false)
                        return $this->errorResponse('Non autorizzato');

                $gas->name = $request->input('name');
                $gas->email = $request->input('email');
                $gas->description = $request->input('description');
                $gas->message = $request->input('message');

                if ($gas->mail_conf == '') {
                        $old_password = '';
                }
                else {
                        $mail = json_decode($gas->mail_conf);
                        $old_password = $mail->password;
                }

                $mail = (object) [
                        'username' => $request->input('mailusername'),
                        'password' => $request->input('mailpassword') == '' ? $old_password : $request->input('mailpassword'),
                        'host' => $request->input('mailserver'),
                        'port' => $request->input('mailport'),
                        'address' => $request->input('mailaddress'),
                        'encryption' => $request->has('mailssl') ? 'tls' : ''
                ];

                $gas->mail_conf = json_encode($mail);

                $gas->save();

                return $this->successResponse();
        }

        public function destroy($id)
        {
        //
        }
}
