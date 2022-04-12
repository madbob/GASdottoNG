<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

use Log;

use App\Events\SluggableCreating;

class Gas extends Model
{
    use HasFactory, AttachableTrait, CreditableTrait, PayableTrait, GASModel, SluggableID, Cachable;

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

    private function availableConfigs()
    {
        return systemParameters('Config');
    }

    public function getConfig($name)
    {
        foreach ($this->configs as $conf) {
            if ($conf->name == $name) {
                return $conf->value;
            }
        }

        $defined = $this->availableConfigs();
        if (!isset($defined[$name])) {
            Log::error(_i('Configurazione GAS non prevista'));
            return '';
        }
        else {
            $this->setConfig($name, $defined[$name]->default());
            $this->load('configs');
            return $this->getConfig($name);
        }
    }

    public function setConfig($name, $value)
    {
        if (is_object($value) || is_array($value)) {
            $value = json_encode($value);
        }

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

    public function getAttribute($key)
    {
        $configs = $this->availableConfigs();
        $c = $configs[$key] ?? null;

        if ($c) {
            return $c->asAttribute($this);
        }
        else {
            return parent::getAttribute($key);
        }
    }

    public function nextInvoiceNumber()
    {
        $status = $this->extra_invoicing;
        $now = date('Y');
        $year = $status['invoices_counter_year'] ?? $now;

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
        return $this->innerCache('feature_' . $name, function($obj) use ($name) {
            switch($name) {
                case 'shipping_places':
                    return ($obj->deliveries->isEmpty() == false);
                case 'rid':
                    return !empty($obj->rid['iban']);
                case 'paypal':
                    return !empty($obj->paypal['client_id']);
                case 'satispay':
                    return !empty($obj->satispay['secret']);
                case 'integralces':
                    return $obj->integralces['enabled'];
                case 'extra_invoicing':
                    return (!empty($obj->extra_invoicing['taxcode']) || !empty($obj->extra_invoicing['vat']));
                case 'public_registrations':
                    return $obj->public_registrations['enabled'];
                case 'auto_aggregates':
                    return Aggregate::has('orders', '>=', Aggregate::aggregatesConvenienceLimit())->count() > 3;
            }

            return false;
        });
    }

    /*************************************************************** GASModel */

    public function getShowURL()
    {
        return route('multigas.show', $this->id);
    }

    /******************************************************** AttachableTrait */

    protected function requiredAttachmentPermission()
    {
        return 'gas.config';
    }

    /******************************************************** CreditableTrait */

    protected function virtualBalances($currency)
    {
        if ($currency) {
            return $this->innerCache('virtual_balances_' . $currency->id, function($obj) use ($currency) {
                $suppliers_balance = sumCurrentBalanceAmounts($currency, Supplier::class);;
                $users_balance = sumCurrentBalanceAmounts($currency, User::class);

                return [
                    'suppliers' => (object) [
                        'label' => _i('Fornitori'),
                        'value' => $suppliers_balance,
                    ],
                    'users' => (object) [
                        'label' => _i('Utenti'),
                        'value' => $users_balance,
                    ],
                ];
            });
        }
        else {
            return [
                'suppliers' => (object) [
                    'label' => _i('Fornitori'),
                ],
                'users' => (object) [
                    'label' => _i('Utenti'),
                ],
            ];
        }
    }

    public function balanceFields()
    {
        $ret = [
            'bank' => _i('Conto Corrente'),
            'cash' => _i('Cassa Contanti'),
            'gas' => _i('GAS'),
            'deposits' => _i('Cauzioni'),
        ];

        $gas = currentAbsoluteGas();

        if ($gas->hasFeature('paypal')) {
            $ret['paypal'] = _i('PayPal');
        }

        if ($gas->hasFeature('satispay')) {
            $ret['satispay'] = _i('Satispay');
        }

        return $ret;
    }
}
