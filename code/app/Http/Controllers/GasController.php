<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Auth;
use DB;

use App\Role;
use App\Gas;
use App\User;

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
        return redirect()->route('gas.edit', $user->gas->id);
    }

    public function show()
    {
        $user = Auth::user();
        return redirect()->route('gas.edit', $user->gas->id);
    }

    public function getLogo($id)
    {
        $gas = Gas::findOrFail($id);
        return downloadFile($gas, 'logo');
    }

    public function edit($id)
    {
        $user = Auth::user();
        $gas = Gas::findOrFail($id);
        if ($user->can('gas.config', $gas) == false) {
            abort(503);
        }

        return view('pages.gas', ['gas' => $gas]);
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        $user = Auth::user();
        $gas = Gas::findOrFail($id);

        if ($user->can('gas.config', $gas) == false) {
            return $this->errorResponse(_i('Non autorizzato'));
        }

        $group = $request->input('group');

        switch($group) {
            case 'general':
                if ($request->hasFile('logo')) {
                    saveFile($request->file($field), $gas, 'logo');
                }

                $gas->name = $request->input('name');
                $gas->email = $request->input('email');
                $gas->message = $request->input('message');
                $gas->setConfig('restricted', $request->has('restricted') ? '1' : '0');
                $gas->setConfig('language', $request->input('language'));
                $gas->setConfig('currency', $request->input('currency'));
                break;

            case 'banking':
                $gas->setConfig('year_closing', decodeDateMonth($request->input('year_closing')));
                $gas->setConfig('annual_fee_amount', $request->input('annual_fee_amount', 0));
                $gas->setConfig('deposit_amount', $request->input('deposit_amount', 0));

                $rid_info = (object) [
                    'iban' => $request->input('rid->iban'),
                    'id' => $request->input('rid->id'),
                    'org' => $request->input('rid->org'),
                ];
                $gas->setConfig('rid', $rid_info);

                $paypal_info = (object) [
                    'client_id' => $request->input('paypal->client_id'),
                    'secret' => $request->input('paypal->secret'),
                    'mode' => $request->input('paypal->mode'),
                ];
                $gas->setConfig('paypal', $paypal_info);

                $invoicing_info = $gas->extra_invoicing;
                $invoicing_info['business_name'] = $request->input('extra_invoicing->business_name');
                $invoicing_info['taxcode'] = $request->input('extra_invoicing->taxcode');
                $invoicing_info['vat'] = $request->input('extra_invoicing->vat');
                $invoicing_info['address'] = $request->input('extra_invoicing->address');
                $gas->setConfig('extra_invoicing', $invoicing_info);

                break;

            case 'users':
                $gas->setConfig('public_registrations', $request->has('public_registrations') ? '1' : '0');
                break;

            case 'orders':
                $gas->setConfig('fast_shipping_enabled', $request->has('fast_shipping_enabled') ? '1' : '0');
                $gas->setConfig('orders_display_columns', $request->input('orders_display_columns'));
                break;

            case 'roles':
                $conf = (object) [
                    'user' => $request->input('roles->user'),
                    'friend' => $request->input('roles->friend'),
                ];

                $old_friend_role = $gas->roles['friend'];
                $update_users = ($conf->friend != $old_friend_role);

                $gas->setConfig('roles', $conf);

                /*
                    Se il ruolo "amico" viene cambiato, cambio effettivamente
                    gli utenti coinvolti
                */
                if ($update_users) {
                    $friends = User::whereNotNull('parent_id')->get();

                    foreach($friends as $friend) {
                        $friend->removeRole($old_friend_role, $gas);
                        $friend->addRole($conf->friend, $gas);
                    }
                }

                break;
        }

        $gas->save();
        return $this->successResponse();
    }
}
