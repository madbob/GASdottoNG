<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\Events\SluggableCreating;
use App\GASModel;
use App\PayableTrait;
use App\CreditableTrait;
use App\SluggableID;

class Invoice extends Model
{
    use GASModel, PayableTrait, CreditableTrait, SluggableID;

    public $incrementing = false;

    protected $dispatchesEvents = [
        'creating' => SluggableCreating::class
    ];

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
        return $this->supplier->orders()->where('status', 'shipped')->whereDoesntHave('invoice', function($query) {
            $query->whereIn('invoices.status', ['verified', 'payed']);
        })->get();
    }

    public function getNameAttribute()
    {
        return sprintf('%s - %s - %s', $this->supplier->name, $this->number, printableDate($this->date));
    }

    public static function statuses()
    {
        return [
            [
                'label' => _i('In Attesa'),
                'value' => 'pending',
            ],
            [
                'label' => _i('Da Verificare'),
                'value' => 'to_verify',
            ],
            [
                'label' => _i('Verificata'),
                'value' => 'verified',
            ],
            [
                'label' => _i('Pagata'),
                'value' => 'payed',
            ]
        ];
    }

    public static function doSort($invoices)
    {
        return $invoices->sort(function($a, $b) {
            if (is_a($a, 'App\Invoice') && is_a($b, 'App\Invoice')) {
                if ($a->status == 'payed' && $a->payment && $b->status == 'payed' && $b->payment)
                    return $a->payment->date <=> $b->payment->date;

                if ($a->status == 'payed')
                    return -1;
                if ($b->status == 'payed')
                    return 1;

                return $a->date <=> $b->date;
            }
            else {
                $a_date = null;
                $b_date = null;

                if (is_a($a, 'App\Invoice')) {
                    if ($a->payment)
                        $a_date = $a->payment->date;
                    else
                        $a_date = $a->date;
                }
                else {
                    $a_date = $a->date;
                }

                if (is_a($b, 'App\Invoice')) {
                    if ($b->payment)
                        $b_date = $b->payment->date;
                    else
                        $b_date = $b->date;
                }
                else {
                    $b_date = $b->date;
                }

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

    public static function balanceFields()
    {
        return [
            'bank' => _i('Saldo Fornitore'),
        ];
    }
}
