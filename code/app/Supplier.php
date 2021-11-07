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

    public function getActiveOrdersAttribute()
    {
        return $this->orders()->whereNotIn('status', ['shipped', 'archived'])->get();
    }

    public function getDisplayURL()
    {
        return URL::action('SuppliersController@show');
    }

    public function involvedEmails()
    {
        $contacts = new Collection();

        $contacts = $contacts->merge($this->contacts()->where('type', 'email')->get());

        $users = everybodyCan('supplier.modify', $this);
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

    public function balanceFields()
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

    public function exportJSON()
    {
        return view('gdxp.json.supplier', ['obj' => $this])->render();
    }
}
