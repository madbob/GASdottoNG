<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\Scopes\RestrictedGAS;
use App\Events\SluggableCreating;

class Invoice extends Model implements AccountingDocument
{
    use GASModel, PayableTrait, CreditableTrait, HierarcableTrait, SluggableID;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $dispatchesEvents = [
        'creating' => SluggableCreating::class
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new RestrictedGAS());
    }

    public static function commonClassName()
    {
        return _i('Fattura');
    }

    public function supplier()
    {
        return $this->belongsTo('App\Supplier')->withTrashed();
    }

    public function payment()
    {
        return $this->belongsTo('App\Movement');
    }

    public function otherMovements()
    {
        return $this->belongsToMany('App\Movement');
    }

    public function orders()
    {
        return $this->belongsToMany('App\Order');
    }

    public function ordersCandidates()
    {
        return $this->supplier->orders()->whereIn('status', ['shipped', 'archived'])->whereNull('payment_id')->whereDoesntHave('invoice', function($query) {
            $query->whereIn('invoices.status', ['verified', 'payed']);
        })->whereHas('bookings', function($query) {
            $query->where('status', 'shipped');
        })->get();
    }

    public function getNameAttribute()
    {
        return sprintf('%s - %s - %s', $this->supplier->name, $this->number, printableDate($this->date));
    }

    public static function statuses()
    {
        return [
            'pending' => _i('In Attesa'),
            'to_verify' => _i('Da Verificare'),
            'verified' => _i('Verificata'),
            'payed' => _i('Pagata'),
        ];
    }

    public static function doSort($invoices)
    {
        return $invoices->sort(function($a, $b) {
            if (is_a($a, Invoice::class) && is_a($b, Invoice::class)) {
                if ($a->status == 'payed' && $a->payment && $b->status == 'payed' && $b->payment) {
                    return $a->payment->date <=> $b->payment->date;
                }
                else if ($a->status == 'payed') {
                    return -1;
                }
                else if ($b->status == 'payed') {
                    return 1;
                }
                else {
                    return $a->date <=> $b->date;
                }
            }
            else {
                $a_date = $a->sorting_date;
                $b_date = $b->sorting_date;
                return $a_date <=> $b_date;
            }
        })->reverse();
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
            'bank' => _i('Saldo Fornitore'),
        ];
    }

    /***************************************************** AccountingDocument */

    public function getSortingDateAttribute()
    {
        if ($this->payment) {
            return $this->payment->date;
        }
        else {
            return $this->date;
        }
    }
}
