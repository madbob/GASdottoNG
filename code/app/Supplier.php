<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\AttachableTrait;
use App\Attachment;
use App\AllowableTrait;
use App\GASModel;
use App\SluggableID;

class Supplier extends Model
{
    use AttachableTrait, AllowableTrait, GASModel, SluggableID;

    public $incrementing = false;

    public function products()
    {
        return $this->hasMany('App\Product')->orderBy('name');
    }

    public function orders()
    {
        return $this->hasMany('App\Order')->orderBy('end', 'desc');
    }

    public function contacts()
    {
        return $this->morphMany('App\Contact', 'target');
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
