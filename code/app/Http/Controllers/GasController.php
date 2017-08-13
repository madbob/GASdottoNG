<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Auth;
use DB;
use Theme;
use App\Role;
use App\Gas;

class GasController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['getLogo']]);

        $this->commonInit([
            'reference_class' => 'App\\Gas'
        ]);
    }

    public function index()
    {
        $user = Auth::user();
        return redirect(url('gas/' . $user->gas->id . '/edit'));
    }

    public function getLogo($id)
    {
        $gas = Gas::findOrFail($id);
        return response()->download(storage_path($gas->logo));
    }

    public function edit($id)
    {
        $user = Auth::user();
        $gas = Gas::findOrFail($id);
        if ($user->can('gas.config', $gas) == false) {
            abort(503);
        }

        return Theme::view('pages.gas', ['gas' => $gas]);
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        $user = Auth::user();
        $gas = Gas::findOrFail($id);

        if ($request->has('year_closing')) {
            /*
                Pannello "Configurazione Contabilità" in gestione movimenti
            */
            if ($user->can('movements.admin', $gas) == false) {
                return $this->errorResponse('Non autorizzato');
            }

            $gas->setConfig('year_closing', decodeDateMonth($request->input('year_closing')));
            $gas->setConfig('annual_fee_amount', $request->input('annual_fee_amount', 0));
            $gas->setConfig('deposit_amount', $request->input('deposit_amount', 0));
        }
        else {
            /*
                Pannello primario "Configurazioni"
            */
            if ($user->can('gas.config', $gas) == false) {
                return $this->errorResponse('Non autorizzato');
            }

            $gas->name = $request->input('name');
            $gas->email = $request->input('email');
            $gas->message = $request->input('message');
            $this->handleDirectFileUpload($request, 'logo', $gas);
            $gas->setConfig('restricted', $request->has('restricted') ? '1' : '0');

            $mailconf = $gas->getConfig('mail_conf');
            if ($mailconf == '') {
                $old_password = '';
            } else {
                $mail = json_decode($mailconf);
                $old_password = $mail->password;
            }

            $mail = (object) [
                'username' => $request->input('mailusername'),
                'password' => $request->input('mailpassword') == '' ? $old_password : $request->input('mailpassword'),
                'host' => $request->input('mailserver'),
                'port' => $request->input('mailport'),
                'address' => $request->input('mailaddress'),
                'encryption' => $request->has('mailssl') ? 'tls' : '',
            ];

            $gas->setConfig('mail_conf', json_encode($mail));

            $rid = (object) [
                'name' => $request->input('ridname'),
                'iban' => $request->input('ridiban'),
                'code' => $request->input('ridcode'),
            ];

            $gas->setConfig('rid_conf', json_encode($rid));
        }

        $gas->save();

        return $this->successResponse();
    }
}
