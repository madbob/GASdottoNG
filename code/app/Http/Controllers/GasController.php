<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Auth;
use DB;
use Log;

use App\Gas;

class GasController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['except' => ['getLogo']]);

        $this->commonInit([
            'reference_class' => 'App\\Gas',
        ]);
    }

    public function index()
    {
        $user = Auth::user();

        return redirect()->route('gas.edit', $user->gas->id);
    }

    public function show()
    {
        return $this->index();
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
        if ($user->can('gas.config', $gas) === false) {
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

        $currency = defaultCurrency();
        $currency->symbol = $request->input('currency', '€');
        $currency->save();

        $gas->setManyConfigs($request, [
            'restricted',
            'multigas',
            'language',
        ]);
    }

    private function configBanking($gas, $request)
    {
        $gas->setManyConfigs($request, [
            'year_closing',
            'annual_fee_amount',
            'deposit_amount',
            'auto_fee',
            'credit_home',
            'rid',
            'satispay',
            'integralces',
            'extra_invoicing',
        ]);
    }

    private function configUsers($gas, $request)
    {
        $gas->setManyConfigs($request, ['public_registrations']);
    }

    private function configProducts($gas, $request)
    {
        $gas->setManyConfigs($request, [
            'manual_products_sorting',
            'products_grid_display_columns',
        ]);
    }

    private function configOrders($gas, $request)
    {
        $gas->setManyConfigs($request, [
            'restrict_booking_to_credit',
            'unmanaged_shipping',
            'booking_contacts',
            'orders_display_columns',
            'orders_shipping_user_columns',
            'orders_shipping_product_columns',
            'orders_shipping_separate_friends',
        ]);
    }

    private function configMails($gas, $request)
    {
        $gas->setManyConfigs($request, [
            'notify_all_new_orders',
            'send_order_reminder',
            'auto_user_order_summary',
            'auto_referent_order_summary',
        ]);

        foreach (array_keys(systemParameters('MailTypes')) as $identifier) {
            if ($request->has('custom_mails_' . $identifier . '_subject')) {
                $gas->setConfig('mail_' . $identifier, (object) [
                    'subject' => $request->input('custom_mails_' . $identifier . '_subject', ''),
                    'body' => $request->input('custom_mails_' . $identifier . '_body', ''),
                ]);
            }
        }
    }

    private function configImport($gas, $request)
    {
        $gas->setManyConfigs($request, [
            'es_integration',
            'csv_separator',
        ]);
    }

    private function configRoles($gas, $request)
    {
        $gas->setManyConfigs($request, [
            'roles',
        ]);
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        $user = Auth::user();
        $gas = Gas::findOrFail($id);

        if ($user->can('gas.config', $gas) === false) {
            return $this->errorResponse(__('texts.generic.unauthorized'));
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
        if ($user->can('gas.config', $user->gas) === false) {
            abort(503);
        }

        $filepath = sprintf('%s/dump_%s', sys_get_temp_dir(), Str::random(20));

        switch (env('DB_CONNECTION')) {
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
