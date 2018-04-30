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
                'default' => '0'
            ],

            'orders_display_columns' => [
                'default' => ['selection', 'name', 'price', 'quantity', 'total_price', 'quantity_delivered', 'price_delivered', 'notes']
            ],

            'paypal' => [
                'default' => (object) [
                    'client_id' => '',
                    'secret' => '',
                    'mode' => 'sandbox'
                ]
            ]
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
        return $this->getConfig('public_registrations') == '1';
    }

    public function getOrdersDisplayColumnsAttribute()
    {
        return (array) json_decode($this->getConfig('orders_display_columns'));
    }

    public function getPaypalAttribute()
    {
        return (array) json_decode($this->getConfig('paypal'));
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
        if(!empty($gas->paypal['client_id']))
            $ret['paypal'] = _i('PayPal');

        return $ret;
    }
}
