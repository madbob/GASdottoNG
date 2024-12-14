<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;


use App\Models\Concerns\AttachableTrait;
use App\Models\Concerns\Configurable;
use App\Models\Concerns\PayableTrait;
use App\Models\Concerns\CreditableTrait;
use App\Events\SluggableCreating;

class Gas extends Model
{
    use AttachableTrait, Cachable, Configurable, CreditableTrait, GASModel, HasFactory, PayableTrait, SluggableID;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'gas';

    protected $dispatchesEvents = [
        'creating' => SluggableCreating::class,
    ];

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
        $this->enableGlobalCache();
    }

    public static function commonClassName()
    {
        return 'GAS';
    }

    public function getLogoUrlAttribute()
    {
        if (empty($this->logo)) {
            return '';
        }
        else {
            return url('gas/' . $this->id . '/logo');
        }
    }

    public function users(): HasMany
    {
        return $this->hasMany('App\User')->orderBy('lastname', 'asc');
    }

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany('App\Supplier')->orderBy('name', 'asc');
    }

    public function aggregates(): BelongsToMany
    {
        return $this->belongsToMany('App\Aggregate')->orderBy('id', 'desc');
    }

    public function deliveries(): BelongsToMany
    {
        return $this->belongsToMany('App\Delivery')->orderBy('name', 'asc');
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
        return $this->innerCache('feature_' . $name, function ($obj) use ($name) {
            switch ($name) {
                case 'shipping_places':
                    return $obj->deliveries->isEmpty() == false;
                case 'rid':
                    return ! empty($obj->rid['iban']);
                case 'satispay':
                    return ! empty($obj->satispay['secret']);
                case 'integralces':
                    return $obj->integralces['enabled'];
                case 'extra_invoicing':
                    return ! empty($obj->extra_invoicing['taxcode']) || ! empty($obj->extra_invoicing['vat']);
                case 'public_registrations':
                    return $obj->public_registrations['enabled'];
                case 'restrict_booking_to_credit':
                    return $obj->restrict_booking_to_credit['enabled'];
                case 'auto_aggregates':
                    return Aggregate::has('orders', '>=', aggregatesConvenienceLimit())->count() > 3;
                case 'send_order_reminder':
                    return $obj->send_order_reminder > 0;
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
        $ret = [
            'suppliers' => (object) [
                'label' => _i('Fornitori'),
            ],
            'users' => (object) [
                'label' => _i('Utenti'),
            ],
        ];

        if ($currency) {
            [$suppliers_balance, $users_balance] = $this->innerCache('virtual_balances_' . $currency->id, function ($obj) use ($currency) {
                $suppliers_balance = sumCurrentBalanceAmounts($currency, Supplier::class);
                $users_balance = sumCurrentBalanceAmounts($currency, User::class);

                return [$suppliers_balance, $users_balance];
            });

            $ret['suppliers']->value = $suppliers_balance;
            $ret['users']->value = $users_balance;
        }

        return $ret;
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

        if ($gas->hasFeature('satispay')) {
            $ret['satispay'] = _i('Satispay');
        }

        return $ret;
    }
}
