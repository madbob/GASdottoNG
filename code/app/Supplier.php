<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Collection;

use Auth;
use Theme;

use App\Events\SluggableCreating;
use App\AttachableTrait;
use App\Attachment;
use App\GASModel;
use App\SluggableID;
use App\Aggregate;
use App\ContactableTrait;
use App\PayableTrait;
use App\ExportableTrait;

class Supplier extends Model
{
    use SoftDeletes, AttachableTrait, ContactableTrait, CreditableTrait, PayableTrait, ExportableTrait, GASModel, SluggableID;

    public $incrementing = false;
    protected $dates = ['deleted_at'];

    protected $events = [
        'creating' => SluggableCreating::class,
    ];

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

    public function scopeFilterEnabled($query)
    {
        $user = Auth::user();
        if ($user->can('supplier.add', $user->gas))
            return $query->withTrashed();
        else
            return $query;
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

    /******************************************************** CreditableTrait */

    public static function balanceFields()
    {
        return [
            'bank' => 'Ordini',
        ];
    }

    /******************************************************** ExportableTrait */

    public function exportXML()
    {
        return Theme::view('gdxp.supplier', ['obj' => $this])->render();
    }

    public static function readXML($xml)
    {
        $supplier = new Supplier();
        $supplier->products = new Collection();
        $supplier->orders = new Collection();

        foreach($xml->children() as $c) {
            switch($c->getName()) {
                case 'name':
                    $supplier->name = $supplier->business_name = (string) $c;
                    break;

                case 'products':
                    foreach($c->children() as $a) {
                        $product = new Product();

                        foreach($a->children() as $p) {
                            switch($p->getName()) {
                                case 'name':
                                    $product->name = (string) $p;
                                    break;
                            }
                        }

                        $supplier->products->push($product);
                    }
                    break;

                case 'orders':
                    foreach($c->children() as $a) {
                        $order = Order::readXML($a);
                        $supplier->orders->push($order);
                    }
                    break;
            }
        }

        return $supplier;
    }

    public static function importXML($xml, $replace)
    {
        if ($replace == null) {
            $supplier = new Supplier();
        }
        else {
            $supplier = Supplier::findOrFail($replace);
            $supplier->products()->update(['archived' => true]);
            $supplier->contacts()->delete();
        }

        $product_ids = [];

        foreach($xml->children() as $c) {
            switch($c->getName()) {
                case 'taxCode':
                    $supplier->taxcode = (string) $c;
                    break;

                case 'vatNumber':
                    $supplier->vat = (string) $c;
                    break;

                case 'name':
                    $name = $supplier->business_name = (string) $c;

                    /*
                        Per impedire collisioni sui nomi dei fornitori (che
                        devono essere univoci)
                    */
                    $index = 2;
                    while(Supplier::where('name', $name)->first() != null)
                        $name = $supplier->business_name . ' ' . $index++;

                    $supplier->name = $name;
                    break;

                case 'contacts':
                    foreach($c->children() as $a) {
                        foreach($a->children() as $p) {
                            foreach($p->children() as $e) {
                                $supplier->save();

                                $contact = new Contact();

                                switch($e->getName()) {
                                    case 'phoneNumber':
                                        $contact->type = 'phone';
                                        break;
                                    case 'faxNumber':
                                        $contact->type = 'fax';
                                        break;
                                    case 'emailAddress':
                                        $contact->type = 'email';
                                        break;
                                    case 'webSite':
                                        $contact->type = 'website';
                                        break;
                                }

                                $contact->value = (string) $e;

                                $contact->target_id = $supplier->id;
                                $contact->target_type = get_class($supplier);
                                $contact->save();
                            }
                        }
                    }
                    break;

                case 'products':
                    foreach($c->children() as $a) {
                        $product = new Product();
                        $supplier->save();
                        $product->supplier_id = $supplier->id;

                        foreach($a->children() as $p) {
                            switch($p->getName()) {
                                case 'sku':
                                    $product->supplier_code = (string) $p;
                                    break;

                                case 'name':
                                    $product->name = (string) $p;
                                    break;

                                case 'category':
                                    $name = (string) $p;
                                    $category = Category::where('name', $name)->first();
                                    if($category == null) {
                                        $category = new Category();
                                        $category->name = $name;
                                        $category->save();
                                    }
                                    $product->category_id = $category->id;
                                    break;

                                case 'um':
                                    $name = (string) $p;
                                    $measure = Measure::where('name', $name)->first();
                                    if($measure == null) {
                                        $measure = new Measure();
                                        $measure->name = $name;
                                        $measure->save();
                                    }
                                    $product->measure_id = $measure->id;
                                    break;

                                case 'description':
                                    $product->description = (string) $p;
                                    break;

                                case 'orderInfo':
                                    foreach($p->children() as $e) {
                                        switch($e->getName()) {
                                            case 'umPrice':
                                                $product->price = (string) $e;
                                                break;
                                            case 'packageQty':
                                                $product->package_size = (string) $e;
                                                break;
                                            case 'minQty':
                                                $product->min_quantity = (string) $e;
                                                break;
                                            case 'maxQty':
                                                $product->max_quantity = (string) $e;
                                                break;
                                            case 'shippingCost':
                                                $product->transport = (string) $e;
                                                break;
                                        }
                                    }
                                    break;

                                case 'variants':
                                    $product->save();

                                    foreach($p->children() as $e) {
                                        $variant = new Variant();

                                        foreach($e->attributes() as $attr_name => $attr_value)
                                            if($attr_name == 'name')
                                                $variant->name = (string) $attr_value;

                                        $variant->product_id = $product->id;
                                        $variant->save();

                                        foreach($e->children() as $i) {
                                            $vv = new VariantValue();
                                            $vv->variant_id = $variant->id;
                                            $vv->value = (string) $i;
                                        }
                                    }
                                    break;
                            }
                        }

                        $product->save();
                        $product_ids[] = $product->id;
                    }
                    break;

                case 'orders':
                    foreach($c->children() as $a) {
                        $aggregate = new Aggregate();
                        $aggregate->save();

                        $order = Order::readXML($a);
                        $order->supplier_id = $supplier->id;
                        $order->aggregate_id = $aggregate->id;
                        $order->status = 'closed';
                        $order->save();
                        $order->products()->attach($product_ids);
                    }
                    break;
            }
        }

        return $supplier;
    }
}
