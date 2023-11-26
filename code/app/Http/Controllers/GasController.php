<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

use Auth;
use DB;
use Log;

use App\Role;
use App\Gas;
use App\User;
use App\Currency;

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

    private function configGeneral($gas, $request)
    {
        handleFileUpload($request->all(), $gas, 'logo');

        $gas->name = $request->input('name');
        $gas->email = $request->input('email');
        $gas->message = $request->input('message');
        $gas->setConfig('restricted', $request->has('restricted') ? '1' : '0');
        $gas->setConfig('language', $request->input('language'));

        $currency = defaultCurrency();
		$currency->symbol = $request->input('currency', '€');
		$currency->save();
    }

    private function configBanking($gas, $request)
    {
        $gas->setConfig('year_closing', decodeDateMonth($request->input('year_closing')));
        $gas->setConfig('annual_fee_amount', $request->input('annual_fee_amount', 0));
        $gas->setConfig('deposit_amount', $request->input('deposit_amount', 0));
        $gas->setConfig('auto_fee', $request->has('auto_fee'));

        if ($request->has('enable_rid')) {
            $rid_info = (object) [
                'iban' => $request->input('rid->iban'),
                'id' => $request->input('rid->id'),
                'org' => $request->input('rid->org'),
            ];
        }
        else {
            $rid_info = (object) [
                'iban' => '',
                'id' => '',
                'org' => '',
            ];
        }

        $gas->setConfig('rid', $rid_info);

        $satispay_info = null;

        if ($request->has('enable_satispay')) {
            $auth_code = $request->input('satispay_auth_code');
            if ($auth_code) {
                try {
                    $authentication = \SatispayGBusiness\Api::authenticateWithToken($auth_code);
                    $satispay_info = (object) [
                        'public' => $authentication->publicKey,
                        'secret' => $authentication->privateKey,
                        'key' => $authentication->keyId,
                    ];
                }
                catch(\Exception $e) {
                    \Log::error('Impossibile completare procedura di verifica su Satispay: ' . $e->getMessage());
                }
            }
        }
        else {
            $satispay_info = (object) [
                'public' => '',
                'secret' => '',
                'key' => '',
            ];
        }

        if ($satispay_info) {
            $gas->setConfig('satispay', $satispay_info);
        }

        if ($request->has('enable_integralces')) {
            $integralces_info = (object) [
                'enabled' => true,
                'identifier' => $request->input('integralces->identifier'),
                'symbol' => $request->input('integralces->symbol'),
            ];
        }
        else {
            $integralces_info = (object) [
                'enabled' => false,
                'identifier' => '',
                'symbol' => '',
            ];
        }

        $gas->setConfig('integralces', $integralces_info);

        if ($request->has('enable_extra_invoicing')) {
            $invoicing_info = $gas->extra_invoicing;
            $invoicing_info['business_name'] = $request->input('extra_invoicing->business_name');
            $invoicing_info['taxcode'] = $request->input('extra_invoicing->taxcode');
            $invoicing_info['vat'] = $request->input('extra_invoicing->vat');
            $invoicing_info['address'] = $request->input('extra_invoicing->address');
            $invoicing_info['invoices_counter_year'] = date('Y');

            $reset_counter = $request->input('extra_invoicing->invoices_counter');
            if (!empty($reset_counter)) {
                $invoicing_info['invoices_counter'] = $reset_counter;
            }
        }
        else {
            $invoicing_info = [
                'business_name' => '',
                'taxcode' => '',
                'vat' => '',
                'address' => '',
                'invoices_counter' => 0,
                'invoices_counter_year' => '',
            ];
        }

        $gas->setConfig('extra_invoicing', $invoicing_info);
    }

    private function configUsers($gas, $request)
    {
        if ($request->has('enable_public_registrations')) {
            $registrations_info = (object) [
                'enabled' => true,
                'privacy_link' => $request->input('public_registrations->privacy_link', ''),
                'terms_link' => $request->input('public_registrations->terms_link', ''),
                'mandatory_fields' => Arr::wrap($request->input('public_registrations->mandatory_fields', [])),
                'manual' => $request->has('public_registrations->manual'),
            ];
        }
        else {
            $registrations_info = (object) [
                'enabled' => false,
                'privacy_link' => '',
                'terms_link' => '',
                'mandatory_fields' => ['firstname', 'lastname', 'email', 'phone'],
                'manual' => false,
            ];
        }

        $gas->setConfig('public_registrations', $registrations_info);
    }

    private function configProducts($gas, $request)
    {
        $gas->setConfig('manual_products_sorting', $request->has('manual_products_sorting') ? '1' : '0');
        $gas->setConfig('products_grid_display_columns', $request->input('products_grid_display_columns', []));
    }

    private function configOrders($gas, $request)
    {
        $gas->setConfig('restrict_booking_to_credit', $request->has('restrict_booking_to_credit') ? '1' : '0');
        $gas->setConfig('unmanaged_shipping', $request->has('unmanaged_shipping') ? '1' : '0');
        $gas->setConfig('booking_contacts', $request->input('booking_contacts'));
        $gas->setConfig('orders_display_columns', $request->input('orders_display_columns', []));
        $gas->setConfig('orders_shipping_user_columns', $request->input('orders_shipping_user_columns', []));
        $gas->setConfig('orders_shipping_product_columns', $request->input('orders_shipping_product_columns', []));
    }

    private function configMails($gas, $request)
    {
        $gas->setConfig('notify_all_new_orders', $request->has('notify_all_new_orders') ? '1' : '0');
        $gas->setConfig('send_order_reminder', $request->has('enable_send_order_reminder') ? $request->input('send_order_reminder') : '0');
        $gas->setConfig('auto_user_order_summary', $request->has('auto_user_order_summary') ? '1' : '0');
        $gas->setConfig('auto_referent_order_summary', $request->has('auto_referent_order_summary') ? '1' : '0');
        $gas->setConfig('auto_supplier_order_summary', $request->has('auto_supplier_order_summary') ? '1' : '0');

        foreach(systemParameters('MailTypes') as $identifier => $metadata) {
            if ($request->has("custom_mails_${identifier}_subject")) {
                $gas->setConfig("mail_${identifier}", (object) [
                    'subject' => $request->input('custom_mails_' . $identifier . '_subject', ''),
                    'body' => $request->input('custom_mails_' . $identifier . '_body', ''),
                ]);
            }
        }
    }

    private function configImport($gas, $request)
    {
        $gas->setConfig('es_integration', $request->has('es_integration') ? '1' : '0');
        $gas->setConfig('csv_separator', $request->input('csv_separator'));
    }

    private function configRoles($gas, $request)
    {
        $role_service = app()->make('RolesService');

        foreach(['user', 'friend', 'multigas'] as $role_type) {
            $input_key = sprintf('roles->%s', $role_type);
            if ($request->has($input_key)) {
                $role = $request->input($input_key);
                $role_service->setMasterRole($gas, $role_type, $role);
    		}
        }
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
        $method = sprintf('config%s', ucwords($group));

        if (method_exists($this, $method)) {
            $this->$method($gas, $request);
        }

        $gas->save();
        return $this->successResponse();
    }

    public function databaseDump(Request $request)
    {
        $user = $request->user();
        if ($user->can('gas.config', $user->gas) == false) {
            abort(503);
        }

        $filepath = sprintf('%s/dump_%s', sys_get_temp_dir(), Str::random(20));

        switch(env('DB_CONNECTION')) {
            case 'mysql':
                \Spatie\DbDumper\Databases\MySql::create()->setDbName(env('DB_DATABASE'))->setUserName(env('DB_USERNAME'))->setPassword(env('DB_PASSWORD'))->dumpToFile($filepath);
                break;

            case 'pgsql':
                \Spatie\DbDumper\Databases\PostgreSql::create()->setDbName(env('DB_DATABASE'))->setUserName(env('DB_USERNAME'))->setPassword(env('DB_PASSWORD'))->dumpToFile($filepath);
                break;

            default:
                Log::error('Formato database non supportato');
                exit();
        }

        return response()->download($filepath, 'database_gasdotto_' . date('Y_m_d') . '.sql')->deleteFileAfterSend();
    }
}
