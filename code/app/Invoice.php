<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use App\Models\Concerns\Datable;
use App\Models\Concerns\PayableTrait;
use App\Models\Concerns\CreditableTrait;
use App\Models\Concerns\HierarcableTrait;
use App\Models\Concerns\AttachableTrait;
use App\Models\Concerns\TracksUpdater;
use App\Scopes\RestrictedGAS;
use App\Events\SluggableCreating;

class Invoice extends Model implements Datable
{
    use AttachableTrait, CreditableTrait, GASModel, HierarcableTrait, PayableTrait, SluggableID, TracksUpdater;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $dispatchesEvents = [
        'creating' => SluggableCreating::class,
    ];

    protected static function boot()
    {
        parent::boot();
        static::initTrackingEvents();
        static::addGlobalScope(new RestrictedGAS());
    }

    public static function commonClassName()
    {
        return __('texts.generic.invoice');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo('App\Supplier')->withTrashed();
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo('App\Movement');
    }

    /*
        Attenzione: benché Invoice sia un PayableTrait tendenzialmente la
        relazione movements dovrebbe essere sempre vuota: il movimento
        principale di pagamento della fattura (di tipo "invoice-payment", e che
        ha come target sempre la medesima Invoice) viene assegnato direttamente
        in payment_id.
        Nella relazione otherMovements si trovano gli altri movimenti creati
        contestualmente al pagamento, che non necessariamente hanno come target
        la fattura (e che pertanto non rientrano nella normale relazione
        movements)
    */
    public function otherMovements(): BelongsToMany
    {
        return $this->belongsToMany('App\Movement');
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany('App\Order');
    }

    public function ordersCandidates()
    {
        return $this->supplier->orders()->whereIn('status', ['shipped', 'archived'])->whereNull('payment_id')->whereDoesntHave('invoice', function ($query) {
            $query->whereIn('invoices.status', ['verified', 'payed']);
        })->whereHas('bookings', function ($query) {
            $query->where('status', 'shipped');
        })->get();
    }

    public function getNameAttribute()
    {
        return sprintf('%s - %s - %s', $this->supplier->name, $this->number, printableDate($this->date));
    }

    public static function doSort($invoices)
    {
        return $invoices->sort(function ($a, $b) {
            if ($a->status == 'payed' && $a->payment && $b->status == 'payed' && $b->payment) {
                return $a->payment->date <=> $b->payment->date;
            }
            elseif ($a->status == 'payed') {
                return -1;
            }
            elseif ($b->status == 'payed') {
                return 1;
            }
            else {
                return $a->date <=> $b->date;
            }
        })->reverse();
    }

    public function totals()
    {
        $orders_total_taxable = 0;
        $orders_total_tax = 0;

        foreach ($this->orders as $order) {
            $summary = $order->calculateInvoicingSummary();
            $orders_total_taxable += $summary->total_taxable;
            $orders_total_tax += $summary->total_tax;
        }

        return [$orders_total_taxable, $orders_total_tax];
    }

    /************************************************************ SluggableID */

    public function getSlugID()
    {
        return sprintf('%s::%s', $this->supplier->id, $this->number);
    }

    /******************************************************** CreditableTrait */

    public function getBalanceProxy()
    {
        return $this->supplier;
    }

    public function balanceFields()
    {
        return [
            'bank' => __('texts.invoices.balances.supplier'),
        ];
    }

    /**************************************************************** Datable */

    public function getSortingDateAttribute()
    {
        if ($this->payment_id != 0 && $this->payment) {
            return $this->payment->date;
        }
        else {
            return $this->date;
        }
    }

    /*********************************************************** PayableTrait */

    public function deleteMovements()
    {
        foreach ($this->movements as $mov) {
            $mov->delete();
        }

        foreach ($this->otherMovements as $mov) {
            $mov->delete();
        }
    }
}
