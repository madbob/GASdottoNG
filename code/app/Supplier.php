<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\AttachableTrait;
use App\Attachment;
use App\GASModel;
use App\SluggableID;
use App\Aggregate;
use App\ContactableTrait;
use App\PayableTrait;

class Supplier extends Model
{
    use SoftDeletes, AttachableTrait, ContactableTrait, CreditableTrait, PayableTrait, GASModel, SluggableID;

    public $incrementing = false;
    protected $dates = ['deleted_at'];

    public static function commonClassName()
    {
        return 'Fornitore';
    }

    public function products()
    {
        return $this->hasMany('App\Product')->with('measure')->where('archived', false)->orderBy('name');
    }

    public function all_products()
    {
        return $this->hasMany('App\Product')->with('measure')->orderBy('name');
    }

    public function orders()
    {
        return $this->hasMany('App\Order')->orderBy('end', 'desc');
    }

    public function bookings()
    {
        return $this->hasManyThrough('App\Booking', 'App\Order');
    }

    public function getAggregatesAttribute()
    {
        $supplier = $this;

        return Aggregate::whereHas('orders', function ($query) use ($supplier) {
            $query->whereIn('id', $supplier->orders->pluck('id'))->orderBy('end', 'desc');
        });
    }

    public function getDisplayURL()
    {
        return Illuminate\Routing\UrlGenerator::action('SuppliersController@show');
    }

    /******************************************************** AttachableTrait */

    protected function requiredAttachmentPermission()
    {
        return 'supplier.modify';
    }

    protected function defaultAttachments()
    {
        $cataloguepdf = new Attachment();
        $cataloguepdf->name = 'Listino PDF (autogenerato)';
        $cataloguepdf->url = url('suppliers/catalogue/'.$this->id.'/pdf');
        $cataloguepdf->internal = true;

        $cataloguecsv = new Attachment();
        $cataloguecsv->name = 'Listino CSV (autogenerato)';
        $cataloguecsv->url = url('suppliers/catalogue/'.$this->id.'/csv');
        $cataloguecsv->internal = true;

        return [$cataloguepdf, $cataloguecsv];
    }

    /*********************************************************** PayableTrait */

    public function queryMovements($query = null, $type = 'all')
    {
        if ($query == null)
            $query = Movement::orderBy('created_at', 'desc');

        $supplier = $this;

        /*
            TODO Le query per filtrare ordini e prenotazioni devono essere migliorate
        */

        switch($type) {
            case 'all':
                $query->where(function($query) use ($supplier) {
                    $query->where(function($query) use ($supplier) {
                        $query->where('sender_type', 'App\Supplier')->where('sender_id', $supplier->id);
                    })->orWhere(function($query) use ($supplier) {
                        $query->where('sender_type', 'App\Order')->whereIn('sender_id', $supplier->orders()->pluck('orders.id'));
                    })->orWhere(function($query) use ($supplier) {
                        $query->where('sender_type', 'App\Booking')->whereIn('sender_id', $supplier->bookings()->pluck('bookings.id'));
                    });
                })->orWhere(function($query) use ($supplier) {
                    $query->where(function($query) use ($supplier) {
                        $query->where('target_type', 'App\Supplier')->where('target_id', $supplier->id);
                    })->orWhere(function($query) use ($supplier) {
                        $query->where('target_type', 'App\Order')->whereIn('target_id', $supplier->orders()->pluck('orders.id'));
                    })->orWhere(function($query) use ($supplier) {
                        $query->where('target_type', 'App\Booking')->whereIn('target_id', $supplier->bookings()->pluck('bookings.id'));
                    });
                });
                break;

            case 'sender':
                $query->where(function($query) use ($supplier) {
                    $query->where(function($query) use ($supplier) {
                        $query->where('sender_type', 'App\Supplier')->where('sender_id', $supplier->id);
                    })->orWhere(function($query) use ($supplier) {
                        $query->where('sender_type', 'App\Order')->whereIn('sender_id', $supplier->orders()->pluck('orders.id'));
                    })->orWhere(function($query) use ($supplier) {
                        $query->where('sender_type', 'App\Booking')->whereIn('sender_id', $supplier->bookings()->pluck('bookings.id'));
                    });
                });
                break;

            case 'target':
                $query->where(function($query) use ($supplier) {
                    $query->where(function($query) use ($supplier) {
                        $query->where('target_type', 'App\Supplier')->where('target_id', $supplier->id);
                    })->orWhere(function($query) use ($supplier) {
                        $query->where('target_type', 'App\Order')->whereIn('target_id', $supplier->orders()->pluck('orders.id'));
                    })->orWhere(function($query) use ($supplier) {
                        $query->where('target_type', 'App\Booking')->whereIn('target_id', $supplier->bookings()->pluck('bookings.id'));
                    });
                });
                break;
        }

        return $query;
    }
}
