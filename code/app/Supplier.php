<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\AttachableTrait;
use App\Attachment;
use App\GASModel;
use App\SluggableID;
use App\Aggregate;
use App\ContactableTrait;

class Supplier extends Model
{
    use AttachableTrait, ContactableTrait, CreditableTrait, GASModel, SluggableID;

    public $incrementing = false;

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

    public function getAggregatesAttribute()
    {
        $supplier = $this;

        return Aggregate::whereHas('orders', function ($query) use ($supplier) {
            $query->whereIn('id', $supplier->orders->pluck('id'))->orderBy('end', 'desc');
        });
    }

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

    public function getDisplayURL()
    {
        return Illuminate\Routing\UrlGenerator::action('SuppliersController@show');
    }
}
