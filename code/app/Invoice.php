<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\GASModel;
use App\PayableTrait;
use App\CreditableTrait;

class Invoice extends Model
{
    use GASModel, PayableTrait, CreditableTrait;

    public static function commonClassName()
    {
        return _i('Fattura');
    }

    public function supplier()
    {
        return $this->belongsTo('App\Supplier');
    }

    public function payment()
    {
        return $this->belongsTo('App\Movement');
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
        return sprintf('%s - %s', $this->supplier->name, $this->number);
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

    /******************************************************** CreditableTrait */

    public function alterBalance($amount, $type = 'bank')
    {
        $this->supplier->alterBalance($amount, $type);
    }

    public static function balanceFields()
    {
        return [
            'bank' => _i('Saldo Fornitore'),
        ];
    }
}
