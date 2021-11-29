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
        \Log::debug($key);
        $c = $configs[$key] ?? null;

        if ($c) {
            return $c->asAttribute($this);
        }
        else {
            \Log::debug('no!');
            return parent::getAttribute($key);
        }
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
            case 'rid':
                return !empty($this->rid['iban']);
            case 'paypal':
                return !empty($this->paypal['client_id']);
            case 'satispay':
                return !empty($this->satispay['secret']);
            case 'extra_invoicing':
                return (!empty($this->extra_invoicing['taxcode']) || !empty($this->extra_invoicing['vat']));
            case 'public_registrations':
                return $this->public_registrations['enabled'];
            case 'auto_aggregates':
                return Aggregate::has('orders', '>=', Aggregate::aggregatesConvenienceLimit())->count() > 3;
        }

        return false;
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

    public function virtualBalances()
    {
        return $this->innerCache('enforced_contacts', function($obj) {
            $suppliers_balance = 0;
            $users_balance = 0;

            foreach($obj->suppliers as $supplier) {
                $suppliers_balance += $supplier->current_balance_amount;
            }

            foreach($obj->users as $user) {
                $users_balance += $user->current_balance_amount;
            }

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
