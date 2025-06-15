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

    public function nextInvoiceNumber()
    {
        $status = $this->getAttribute('extra_invoicing');
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

    public function hasFeature($name): bool
    {
        return $this->innerCache('feature_' . $name, function ($obj) use ($name) {
            switch ($name) {
                case 'rid':
                    $ret = ! empty($obj->rid['iban']);
                    break;
                case 'satispay':
                    $ret = ! empty($obj->satispay['secret']);
                    break;
                case 'integralces':
                    $ret = $obj->integralces['enabled'];
                    break;
                case 'extra_invoicing':
                    $ret = ! empty($obj->extra_invoicing['taxcode']) || ! empty($obj->extra_invoicing['vat']);
                    break;
                case 'public_registrations':
                    $ret = $obj->public_registrations['enabled'];
                    break;
                case 'restrict_booking_to_credit':
                    $ret = $obj->restrict_booking_to_credit['enabled'];
                    break;
                case 'auto_aggregates':
                    $ret = Aggregate::has('orders', '>=', aggregatesConvenienceLimit())->count() > 3;
                    break;
                case 'send_order_reminder':
                    $ret = $obj->send_order_reminder > 0;
                    break;
                default:
                    $ret = false;
                    break;
            }

            return $ret;
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
                'label' => __('texts.supplier.all'),
                'class' => Supplier::class,
            ],
            'users' => (object) [
                'label' => __('texts.user.all'),
                'class' => User::class,
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

    /*
        TODO: questa funzione dovrebbe essere statica. Viene ampiamente usata in
        movementtypes/edit.blade.php per recuperare i saldi da formattare nel
        pannello, ma usando istanze create sul momento; l'accesso a $this per
        determinare l'abilitazione di Satispay spacca tutto
    */
    public function balanceFields()
    {
        $ret = [
            'bank' => __('texts.movements.bank_account'),
            'cash' => __('texts.movements.cash_account'),
            'gas' => __('texts.generic.gas'),
            'deposits' => __('texts.movements.deposits'),
        ];

        $gas = currentAbsoluteGas();

        if ($gas->hasFeature('satispay')) {
            $ret['satispay'] = 'Satispay';
        }

        return $ret;
    }
}
