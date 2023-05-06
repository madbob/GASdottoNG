<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

use Auth;
use URL;

use App\Models\Concerns\AttachableTrait;
use App\Models\Concerns\ContactableTrait;
use App\Models\Concerns\PayableTrait;
use App\Models\Concerns\CreditableTrait;
use App\Models\Concerns\ExportableTrait;
use App\Models\Concerns\SuspendableTrait;
use App\Models\Concerns\ModifiableTrait;
use App\Models\Concerns\WithinGas;
use App\Events\SluggableCreating;
use App\Events\AttachableToGas;

class Supplier extends Model
{
    use HasFactory, Notifiable, SoftDeletes,
        AttachableTrait, ContactableTrait, CreditableTrait, PayableTrait, ExportableTrait, SuspendableTrait, ModifiableTrait,
        GASModel, SluggableID, WithinGas, Cachable;

    public $incrementing = false;
    protected $dates = ['deleted_at'];

    protected $dispatchesEvents = [
        'creating' => SluggableCreating::class,
        'created' => AttachableToGas::class,
    ];

    public static function commonClassName()
    {
        return _i('Fornitore');
    }

    public function products(): HasMany
    {
        return $this->hasMany('App\Product')->with('measure');
    }

    public function orders(): HasMany
    {
        return $this->hasMany('App\Order')->orderBy('end', 'asc');
    }

    public function bookings(): HasManyThrough
    {
        return $this->hasManyThrough('App\Booking', 'App\Order');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany('App\Invoice');
    }

    public function calendarDates(): MorphMany
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

    public function involvedEmails()
    {
        $contacts = $this->contacts()->where('type', 'email')->get();

        $users = everybodyCan('supplier.modify', $this);
        foreach($users as $u) {
            $contacts = $contacts->merge($u->contacts()->where('type', 'email')->get());
        }

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

    private function innerQuery($query, $direction, $supplier, $with_invoices)
    {
        $type = $direction . '_type';
        $id = $direction . '_id';

        $query->where(function($query) use ($supplier, $type, $id, $with_invoices) {
            $query->where(function($query) use ($supplier, $type, $id) {
                $query->where($type, 'App\Supplier')->where($id, $supplier->id);
            })->orWhere(function($query) use ($supplier, $type, $id) {
                $query->where($type, 'App\Order')->whereIn($id, $supplier->orders()->pluck('orders.id'));
            })->orWhere(function($query) use ($supplier, $type, $id) {
                $query->where($type, 'App\Booking')->whereIn($id, $supplier->bookings()->pluck('bookings.id'));
            });

            if ($with_invoices) {
                $query->orWhere(function($query) use ($supplier, $type, $id) {
                    $query->where($type, 'App\Invoice')->whereIn($id, $supplier->invoices()->pluck('invoices.id'));
                });
            }
        });
    }

    public function queryMovements($query = null, $type = 'all')
    {
        if (is_null($query)) {
            $query = Movement::orderBy('created_at', 'desc');
        }

        $supplier = $this;

        switch($type) {
            case 'all':
                $query->where(function($query) use ($supplier) {
                    $query->where(function($query) use ($supplier) {
                        $this->innerQuery($query, 'sender', $supplier, true);
                    })->orWhere(function($query) use ($supplier) {
                        $this->innerQuery($query, 'target', $supplier, true);
                    });
                });
                break;

            case 'sender':
                $this->innerQuery($query, 'sender', $supplier, false);
                break;

            case 'target':
                $this->innerQuery($query, 'target', $supplier, false);
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
