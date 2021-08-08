<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;

use Auth;
use URL;

use App\Scopes\RestrictedGAS;
use App\Events\SluggableCreating;
use App\Events\AttachableToGas;

class Supplier extends Model
{
    use HasFactory, Notifiable, SoftDeletes, AttachableTrait, ContactableTrait, CreditableTrait, PayableTrait, ExportableTrait, SuspendableTrait, ModifiableTrait, GASModel, SluggableID;

    public $incrementing = false;
    protected $dates = ['deleted_at'];

    protected $dispatchesEvents = [
        'creating' => SluggableCreating::class,
        'created' => AttachableToGas::class,
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new RestrictedGAS());
    }

    public function gas()
    {
        return $this->belongsToMany('App\Gas');
    }

    public static function commonClassName()
    {
        return _i('Fornitore');
    }

    public function products()
    {
        return $this->hasMany('App\Product')->with('measure')->orderBy('name');
    }

    public function all_products()
    {
        return $this->hasMany('App\Product')->with('measure')->orderBy('name');
    }

    public function orders()
    {
        return $this->hasMany('App\Order')->orderBy('end', 'asc');
    }

    public function bookings()
    {
        return $this->hasManyThrough('App\Booking', 'App\Order');
    }

    public function invoices()
    {
        return $this->hasMany('App\Invoice');
    }

    public function calendarDates()
    {
        return $this->morphMany('App\Date', 'target')->orderBy('date', 'asc');
    }

    public function scopeFilterEnabled($query)
    {
        $user = Auth::user();
        if ($user->can('supplier.add', $user->gas))
            return $query->withTrashed();
        else
            return $query;
    }

    public function getDisplayURL()
    {
        return URL::action('SuppliersController@show');
    }

    public function involvedEmails()
    {
        $contacts = new Collection();

        $contacts = $contacts->merge($this->contacts()->where('type', 'email')->get());

        $users = Role::everybodyCan('supplier.modify', $this);
        foreach($users as $u)
            $contacts = $contacts->merge($u->contacts()->where('type', 'email')->get());

        return $contacts;
    }

    /******************************************************** AttachableTrait */

    protected function requiredAttachmentPermission()
    {
        return 'supplier.modify';
    }

    protected function defaultAttachments()
    {
        $cataloguepdf = new Attachment();
        $cataloguepdf->name = _i('Listino PDF (autogenerato)');
        $cataloguepdf->url = url('suppliers/catalogue/' . $this->id . '?format=pdf');
        $cataloguepdf->internal = true;

        $cataloguecsv = new Attachment();
        $cataloguecsv->name = _i('Listino CSV (autogenerato)');
        $cataloguecsv->url = url('suppliers/catalogue/' . $this->id . '?format=csv');
        $cataloguecsv->internal = true;

        return [$cataloguepdf, $cataloguecsv];
    }

    /*********************************************************** PayableTrait */

    public function queryMovements($query = null, $type = 'all')
    {
        if (is_null($query))
            $query = Movement::orderBy('created_at', 'desc');

        $supplier = $this;

        /*
            TODO Le query per filtrare ordini e prenotazioni devono essere migliorate
        */

        switch($type) {
            case 'all':
                $query->where(function($query) use ($supplier) {
                    $query->where(function($query) use ($supplier) {
                        $query->where(function($query) use ($supplier) {
                            $query->where('sender_type', 'App\Supplier')->where('sender_id', $supplier->id);
                        })->orWhere(function($query) use ($supplier) {
                            $query->where('sender_type', 'App\Order')->whereIn('sender_id', $supplier->orders()->pluck('orders.id'));
                        })->orWhere(function($query) use ($supplier) {
                            $query->where('sender_type', 'App\Booking')->whereIn('sender_id', $supplier->bookings()->pluck('bookings.id'));
                        })->orWhere(function($query) use ($supplier) {
                            $query->where('sender_type', 'App\Invoice')->whereIn('sender_id', $supplier->invoices()->pluck('invoices.id'));
                        });
                    })->orWhere(function($query) use ($supplier) {
                        $query->where(function($query) use ($supplier) {
                            $query->where('target_type', 'App\Supplier')->where('target_id', $supplier->id);
                        })->orWhere(function($query) use ($supplier) {
                            $query->where('target_type', 'App\Order')->whereIn('target_id', $supplier->orders()->pluck('orders.id'));
                        })->orWhere(function($query) use ($supplier) {
                            $query->where('target_type', 'App\Booking')->whereIn('target_id', $supplier->bookings()->pluck('bookings.id'));
                        })->orWhere(function($query) use ($supplier) {
                            $query->where('target_type', 'App\Invoice')->whereIn('target_id', $supplier->invoices()->pluck('invoices.id'));
                        });
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
            'bank' => _i('Ordini'),
        ];
    }

    /******************************************************** ExportableTrait */

    public function exportXML()
    {
        return view('gdxp.xml.supplier', ['obj' => $this])->render();
    }

    public static function readXML($xml)
    {
        $supplier = new Supplier();
        $supplier->products = new Collection();
        $supplier->orders = new Collection();

        foreach($xml->children() as $c) {
            switch($c->getName()) {
                case 'name':
                    $supplier->name = $supplier->business_name = html_entity_decode((string) $c);
                    break;

                case 'products':
                    foreach($c->children() as $a) {
                        $product = new Product();

                        foreach($a->children() as $p) {
                            switch($p->getName()) {
                                case 'name':
                                    $product->name = html_entity_decode((string) $p);
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
        if (is_null($replace)) {
            $supplier = new Supplier();
            $supplier->payment_method = '';
            $supplier->order_method = '';
        }
        else {
            $supplier = Supplier::findOrFail($replace);
            $supplier->contacts()->delete();
        }

        $product_ids = [];

        foreach($xml->children() as $c) {
            switch($c->getName()) {
                case 'taxCode':
                    $supplier->taxcode = html_entity_decode((string) $c);
                    break;

                case 'vatNumber':
                    $supplier->vat = html_entity_decode((string) $c);
                    break;

                case 'name':
                    $name = $supplier->business_name = html_entity_decode((string) $c);

                    /*
                        Per evitare collisioni sui nomi dei fornitori
                    */
                    $index = 2;
                    while(Supplier::where('name', $name)->first() != null)
                        $name = $supplier->business_name . ' ' . $index++;

                    $supplier->name = $name;
                    break;

                case 'contacts':
                    $supplier->save();

                    foreach($c->children() as $a) {
                        foreach($a->children() as $p) {
                            foreach($p->children() as $e) {
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

                                $contact->value = html_entity_decode((string) $e);

                                $contact->target_id = $supplier->id;
                                $contact->target_type = get_class($supplier);
                                $contact->save();
                            }
                        }
                    }
                    break;

                case 'products':
                    $supplier->save();

                    foreach($c->children() as $a) {
                        $product_name = null;

                        foreach($a->children() as $p) {
                            if($p->getName() == 'name') {
                                $product_name = html_entity_decode((string) $p);
                                break;
                            }
                        }

                        if (is_null($product_name)) {
                            continue;
                        }

                        $product = $supplier->products()->where('name', $product_name)->first();
                        if (is_null($product)) {
                            $product = new Product();
                            $product->supplier_id = $supplier->id;
                        }

                        foreach($a->children() as $p) {
                            switch($p->getName()) {
                                case 'sku':
                                    $product->supplier_code = html_entity_decode((string) $p);
                                    break;

                                case 'name':
                                    $product->name = html_entity_decode((string) $p);
                                    break;

                                case 'category':
                                    $name = html_entity_decode((string) $p);
                                    $category = Category::where('name', $name)->first();
                                    if(is_null($category)) {
                                        $category = new Category();
                                        $category->name = $name;
                                        $category->save();
                                    }
                                    $product->category_id = $category->id;
                                    break;

                                case 'um':
                                    $name = html_entity_decode((string) $p);
                                    $measure = Measure::where('name', $name)->first();
                                    if(is_null($measure)) {
                                        $measure = new Measure();
                                        $measure->name = $name;
                                        $measure->save();
                                    }
                                    $product->measure_id = $measure->id;
                                    break;

                                case 'description':
                                    $product->description = html_entity_decode((string) $p);
                                    break;

                                case 'active':
                                    $product->active = (strtolower((string) $p) == 'true');
                                    break;

                                case 'orderInfo':
                                    foreach($p->children() as $e) {
                                        switch($e->getName()) {
                                            case 'umPrice':
                                                $product->price = html_entity_decode((string) $e);
                                                break;
                                            case 'packageQty':
                                                $product->package_size = html_entity_decode((string) $e);
                                                break;
                                            case 'minQty':
                                                $product->min_quantity = html_entity_decode((string) $e);
                                                break;
                                            case 'maxQty':
                                                $product->max_quantity = html_entity_decode((string) $e);
                                                break;
                                            case 'shippingCost':
                                                /*
                                                    TODO: agganciare un modificatore che rappresenti il costo di trasporto statico
                                                */
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
                                            $vv->value = html_entity_decode((string) $i);
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

    public function exportJSON()
    {
        return view('gdxp.json.supplier', ['obj' => $this])->render();
    }

    public static function readJSON($json)
    {
        $supplier = new Supplier();

        $supplier->name = $json->name;
        $supplier->vat = $json->vatNumber ?? '';

        $supplier->products = new Collection();
        foreach($json->products as $a) {
            $product = new Product();
            $product->name = $a->name;
            $supplier->products->push($product);
        }

        $supplier->orders = new Collection();
        if (isset($json->order)) {
            $order = Order::readJSON($json->order);
            $supplier->orders->push($order);
        }

        return $supplier;
    }

    public static function importJSON($master, $json, $replace)
    {
        if (is_null($replace)) {
            $supplier = new Supplier();
            $supplier->payment_method = '';
            $supplier->order_method = '';
        }
        else {
            $supplier = Supplier::findOrFail($replace);
            $supplier->contacts()->delete();
        }

        $product_ids = [];

        $supplier->name = $json->name;
        $supplier->remote_lastimport = $master->creationDate ?? date('Y-m-d');
        $supplier->taxcode = $json->taxCode ?? '';
        $supplier->vat = $json->vatNumber ?? '';
        $supplier->save();

        foreach($json->contacts as $c) {
            if (empty($c->value)) {
                continue;
            }

            $contact = new Contact();

            switch($c->type) {
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

            $contact->value = $c->value;

            $contact->target_id = $supplier->id;
            $contact->target_type = get_class($supplier);
            $contact->save();
        }

        if (!empty($json->address->locality)) {
            $contact = new Contact();
            $contact->type = 'address';
            $contact->value = normalizeAddress($json->address->street, $json->address->locality, $json->address->zipCode);
            $contact->target_id = $supplier->id;
            $contact->target_type = get_class($supplier);
            $contact->save();
        }

        $product_ids = [];

        foreach($json->products as $json_product) {
            $product = $supplier->products()->where('name', $json_product->name)->first();
            if (is_null($product)) {
                $product = new Product();
                $product->supplier_id = $supplier->id;
            }

            $product->name = $json_product->name;
            $product->supplier_code = $json_product->sku ?? '';
            $product->description = $json_product->description ?? '';
            $product->active = $json_product->active ?? true;
            $product->price = (float) ($json_product->orderInfo->umPrice ?? 0);

            $product->package_size = (float) ($json_product->orderInfo->packageQty ?? 0);
            if ($product->package_size == 1) {
                $product->package_size = 0;
            }

            $product->min_quantity = (float) ($json_product->orderInfo->minQty ?? 0);
            $product->max_quantity = (float) ($json_product->orderInfo->maxQty ?? 0);
            $product->multiple = (float) ($json_product->orderInfo->mulQty ?? 0);
            $product->transport = (float) ($json_product->orderInfo->shippingCost ?? 0);
            $product->max_available = (float) ($json_product->orderInfo->availableQty ?? 0);

            /*
                TODO: agganciare un modificatore che rappresenti il costo di
                trasporto statico, col valore di
                $json_product->orderInfo->shippingCost
            */

            $name = $json_product->category ?? '';
            if (!empty($name)) {
                $category = Category::where('name', $name)->first();
                if(is_null($category)) {
                    $category = new Category();
                    $category->name = $name;
                    $category->save();
                }
                $product->category_id = $category->id;
            }

            $name = $json_product->um ?? '';
            if (!empty($name)) {
                $measure = Measure::where('name', $name)->first();
                if(is_null($measure)) {
                    $measure = new Measure();
                    $measure->name = $name;
                    $measure->save();
                }
                $product->measure_id = $measure->id;
            }

            $name = $json_product->orderInfo->vatRate ?? null;
            if (!is_null($name)) {
                $name = (float) $name;
                $vat_rate = VatRate::where('percentage', $name)->first();
                if(is_null($vat_rate)) {
                    $vat_rate = new VatRate();
                    $vat_rate->name = sprintf('%s%%', $name);
                    $vat_rate->percentage = $name;
                    $vat_rate->save();
                }
                $product->vat_rate_id = $vat_rate->id;
            }

            $product->save();

            $product_ids[] = $product->id;
        }

        return $supplier;
    }
}
