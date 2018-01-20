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

    protected $events = [
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

            'mail_conf' => [
                'default' => (object) [
                    'driver' => '',
                    'username' => '',
                    'password' => '',
                    'host' => '',
                    'port' => '',
                    'address' => '',
                    'encryption' => ''
                ]
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
        if (is_object($value))
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

    private function mailConfig()
    {
        $conf = $this->getConfig('mail_conf');
        if ($conf == '') {
            return (object) [
                'driver' => !empty(config('services.ses.key')) ? 'ses' : 'smtp',
                'username' => '',
                'password' => '',
                'host' => '',
                'port' => '',
                'address' => '',
                'encryption' => '',
            ];
        } else {
            return json_decode($conf);
        }
    }

    public function getMailAttribute()
    {
        return (array) $this->mailConfig();
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

    /******************************************************** AttachableTrait */

    protected function requiredAttachmentPermission()
    {
        return 'gas.config';
    }

    /******************************************************** CreditableTrait */

    public static function balanceFields()
    {
        return [
            'bank' => _i('Conto Corrente'),
            'cash' => _i('Cassa Contanti'),
            'gas' => _i('GAS'),
            'suppliers' => _i('Fornitori'),
            'deposits' => _i('Cauzioni'),
        ];
    }
}
