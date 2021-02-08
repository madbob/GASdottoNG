<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Log;

use App\Events\SluggableCreating;

use App\Role;
use App\AttachableTrait;
use App\GASModel;
use App\SluggableID;

class Gas extends Model
{
    use AttachableTrait, CreditableTrait, PayableTrait, GASModel, SluggableID;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $table = 'gas';

    protected $dispatchesEvents = [
        'creating' => SluggableCreating::class,
    ];

    public static function commonClassName()
    {
        return 'GAS';
    }

    public function getLogoUrlAttribute()
    {
        if (empty($this->logo))
            return '';
        else
            return url('gas/' . $this->id . '/logo');
    }

    public function users()
    {
        return $this->hasMany('App\User')->orderBy('lastname', 'asc');
    }

    public function suppliers()
    {
        return $this->belongsToMany('App\Supplier')->orderBy('name', 'asc');
    }

    public function aggregates()
    {
        return $this->belongsToMany('App\Aggregate')->orderBy('id', 'desc');
    }

    public function deliveries()
    {
        return $this->belongsToMany('App\Delivery')->orderBy('name', 'asc');
    }

    public function configs()
    {
        return $this->hasMany('App\Config');
    }

    private function handlingConfigs()
    {
        $default_role = Role::where('name', 'Utente')->first();

        return [
            'year_closing' => [
                'default' => date('Y') . '-09-01'
            ],

            'annual_fee_amount' => [
                'default' => 10.00
            ],

            'deposit_amount' => [
                'default' => 10.00
            ],

            'restricted' => [
                'default' => '0'
            ],

            'fast_shipping_enabled' => [
                'default' => '0'
            ],

            'restrict_booking_to_credit' => [
                'default' => '0'
            ],

            'notify_all_new_orders' => [
                'default' => '0'
            ],

            'auto_user_order_summary' => [
                'default' => '0'
            ],

            'auto_supplier_order_summary' => [
                'default' => '0'
            ],

            'rid' => [
                'default' => (object) [
                    'iban' => '',
                    'id' => '',
                    'org' => ''
                ]
            ],

            'roles' => [
                'default' => (object) [
                    'user' => $default_role ? $default_role->id : -1,
                    'friend' => $default_role ? $default_role->id : -1
                ]
            ],

            'language' => [
                'default' => 'it_IT'
            ],

            'currency' => [
                'default' => '€'
            ],

            'public_registrations' => [
                'default' => (object) [
                    'enabled' => false,
                    'privacy_link' => 'http://gasdotto.net/privacy',
                    'terms_link' => '',
                    'mandatory_fields' => ['firstname', 'lastname', 'email', 'phone']
                ]
            ],

            'orders_display_columns' => [
                'default' => ['selection', 'name', 'price', 'quantity', 'total_price', 'quantity_delivered', 'price_delivered', 'notes']
            ],

            'booking_contacts' => [
                'default' => 'none',
            ],

            'paypal' => [
                'default' => (object) [
                    'client_id' => '',
                    'secret' => '',
                    'mode' => 'sandbox'
                ]
            ],

            'satispay' => [
                'default' => (object) [
                    'secret' => ''
                ]
            ],

            'extra_invoicing' => [
                'default' => (object) [
                    'business_name' => '',
                    'taxcode' => '',
                    'vat' => '',
                    'address' => '',
                    'invoices_counter' => 0,
                    'invoices_counter_year' => date('Y'),
                ]
            ],

            'mail_welcome_subject' => [
                'default' => _i("Benvenuto!"),
            ],
            'mail_welcome_body' => [
                'default' => _i("Benvenuto in %[gas_name]!\nIn futuro potrai accedere usando il link qui sotto, lo username \"%[username]\" e la password da te scelta.\n%[gas_login_link]\nUna mail di notifica è stata inviata agli amministratori."),
            ],

            'mail_password_reset_subject' => [
                'default' => _i("Recupero Password"),
            ],
            'mail_password_reset_body' => [
                'default' => _i("È stato chiesto l'aggiornamento della tua password su GASdotto.\nClicca il link qui sotto per aggiornare la tua password, o ignora la mail se non hai chiesto tu questa operazione.\n%[gas_reset_link]"),
            ],

            'mail_new_order_subject' => [
                'default' => _i("Nuovo Ordine Aperto per %[supplier_name]"),
            ],
            'mail_new_order_body' => [
                'default' => _i("È stato aperto da %[gas_name] un nuovo ordine per il fornitore %[supplier_name].\nPer partecipare, accedi al seguente indirizzo:\n%[gas_booking_link]\nLe prenotazioni verranno chiuse %[closing_date]"),
            ],

            'mail_receipt_subject' => [
                'default' => _i("Nuova fattura da %[gas_name]"),
            ],
            'mail_receipt_body' => [
                'default' => _i("In allegato l'ultima fattura da %[gas_name]")
            ],
        ];
    }

    public function getConfig($name)
    {
        foreach ($this->configs as $conf) {
            if ($conf->name == $name) {
                return $conf->value;
            }
        }

        $defined = self::handlingConfigs();
        if (!isset($defined[$name])) {
            Log::error(_i('Configurazione GAS non prevista'));
            return '';
        }
        else {
            $this->setConfig($name, $defined[$name]['default']);
            $this->load('configs');
            return $this->getConfig($name);
        }
    }

    public function setConfig($name, $value)
    {
        if (is_object($value) || is_array($value))
            $value = json_encode($value);

        foreach ($this->configs as $conf) {
            if ($conf->name == $name) {
                $conf->value = $value;
                $conf->save();
                return;
            }
        }

        $conf = new Config();
        $conf->name = $name;
        $conf->value = $value;
        $conf->gas_id = $this->id;
        $conf->save();
    }

    public function getRidAttribute()
    {
        return (array) json_decode($this->getConfig('rid'));
    }

    public function getRolesAttribute()
    {
        return (array) json_decode($this->getConfig('roles'));
    }

    public function getFastShippingEnabledAttribute()
    {
        return $this->getConfig('fast_shipping_enabled') == '1';
    }

    public function getRestrictBookingToCreditAttribute()
    {
        return $this->getConfig('restrict_booking_to_credit') == '1';
    }

    public function getNotifyAllNewOrdersAttribute()
    {
        return $this->getConfig('notify_all_new_orders') == '1';
    }

    public function getAutoUserOrderSummaryAttribute()
    {
        return $this->getConfig('auto_user_order_summary') == '1';
    }

    public function getAutoSupplierOrderSummaryAttribute()
    {
        return $this->getConfig('auto_supplier_order_summary') == '1';
    }

    public function getRestrictedAttribute()
    {
        return $this->getConfig('restricted') == '1';
    }

    public function getLanguageAttribute()
    {
        return $this->getConfig('language');
    }

    public function getCurrencyAttribute()
    {
        return $this->getConfig('currency');
    }

    public function getPublicRegistrationsAttribute()
    {
        return (array) json_decode($this->getConfig('public_registrations'));
    }

    public function getOrdersDisplayColumnsAttribute()
    {
        return (array) json_decode($this->getConfig('orders_display_columns'));
    }

    public function getBookingContactsAttribute()
    {
        return $this->getConfig('booking_contacts');
    }

    public function getPaypalAttribute()
    {
        return (array) json_decode($this->getConfig('paypal'));
    }

    public function getSatispayAttribute()
    {
        return (array) json_decode($this->getConfig('satispay'));
    }

    public function getExtraInvoicingAttribute()
    {
        return (array) json_decode($this->getConfig('extra_invoicing'));
    }

    public function nextInvoiceNumber()
    {
        $status = $this->extra_invoicing;
        $now = date('Y');
        $year = $status['invoices_counter_year'];

        if ($now == $year) {
            $ret = $status['invoices_counter'] + 1;
        }
        else {
            $ret = 1;
            $status['invoices_counter_year'] = $now;
        }

        $status['invoices_counter'] = $ret;
        $this->setConfig('extra_invoicing', $status);

        return sprintf('%s/%s', $ret, $now);
    }

    public function hasFeature($name)
    {
        switch($name) {
            case 'shipping_places':
                return ($this->deliveries->isEmpty() == false);
                break;
            case 'rid':
                return !empty($this->rid['iban']);
                break;
            case 'paypal':
                return !empty($this->paypal['client_id']);
                break;
            case 'satispay':
                return !empty($this->satispay['secret']);
                break;
            case 'extra_invoicing':
                return (!empty($this->extra_invoicing['taxcode']) || !empty($this->extra_invoicing['vat']));
                break;
            case 'public_registrations':
                return $this->public_registrations['enabled'];
                break;
        }

        return false;
    }

    /******************************************************** AttachableTrait */

    protected function requiredAttachmentPermission()
    {
        return 'gas.config';
    }

    /******************************************************** CreditableTrait */

    public static function balanceFields()
    {
        $ret = [
            'bank' => _i('Conto Corrente'),
            'cash' => _i('Cassa Contanti'),
            'gas' => _i('GAS'),
            'suppliers' => _i('Fornitori'),
            'deposits' => _i('Cauzioni'),
        ];

        $gas = currentAbsoluteGas();
        if($gas->hasFeature('paypal'))
            $ret['paypal'] = _i('PayPal');
        if($gas->hasFeature('satispay'))
            $ret['satispay'] = _i('Satispay');

        return $ret;
    }
}
