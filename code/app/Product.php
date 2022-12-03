<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

use Illuminate\Support\Str;

use App;
use Log;

use App\Events\VariantChanged;
use App\Events\SluggableCreating;

class Product extends Model
{
    use HasFactory, SoftDeletes, ModifiableTrait, GASModel, SluggableID, Cachable;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $dispatchesEvents = [
        'creating' => SluggableCreating::class,
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo('App\Category');
    }

    public function measure()
    {
        return $this->belongsTo('App\Measure');
    }

    public function supplier()
    {
        return $this->belongsTo('App\Supplier');
    }

    public function orders()
    {
        return $this->belongsToMany('App\Order');
    }

    public function variants()
    {
        return $this->hasMany('App\Variant')->with('values')->orderBy('name', 'asc');
    }

    public function vat_rate()
    {
        return $this->belongsTo('App\VatRate');
    }

    public function scopeSorted($query)
    {
        if (currentAbsoluteGas()->manual_products_sorting) {
            $query->orderBy('products.sorting')->orderBy('products.name');
        }
        else {
            $query->orderBy('products.name');
        }
    }

    public function getSlugID()
    {
        return sprintf('%s::%s', $this->supplier_id, Str::slug($this->name));
    }

    public function getPictureUrlAttribute()
    {
        if (empty($this->picture))
            return '';
        else
            return url('products/picture/' . $this->id);
    }

    public function getFixedPackageSizeAttribute()
    {
        if ($this->portion_quantity <= 0) {
            return $this->package_size;
        }
        else {
            return round($this->portion_quantity * $this->package_size, 2);
        }
    }

    public function getVariantCombosAttribute()
    {
        return $this->innerCache('variant_combos', function($obj) {
            $ret = VariantCombo::whereHas('values', function($query) use ($obj) {
                $query->whereHas('variant', function($query) use ($obj) {
                    $query->where('product_id', $obj->id);
                });
            })->with(['values'])->get();

            /*
                Per scrupolo qui faccio un controllo: se il prodotto ha delle
                varianti ma nessuna combo, ne forzo qui la rigenerazione
            */
            if ($ret->isEmpty() && $this->variants()->count() != 0) {
                foreach($this->variants as $variant) {
                    VariantChanged::dispatch($variant);
                }

                return $this->getVariantCombosAttribute();
            }
            else {
                return $ret;
            }
        });
    }

    public function getSortedVariantCombosAttribute()
    {
        return $this->variant_combos->where('active', true)->sortBy(function($combo, $key) {
            return $combo->values->pluck('value')->join(' ');
        }, SORT_NATURAL);
    }

    public function getCategoryNameAttribute()
    {
        $cat = $this->category;
        if ($cat)
            return $cat->name;
        else
            return '';
    }

    public function bookingsInOrder($order)
    {
        $id = $this->id;

        return Booking::where('order_id', '=', $order->id)->whereHas('products', function ($query) use ($id) {
            $query->where('product_id', '=', $id);
        })->get();
    }

    public function printablePrice($variant = null)
    {
        $price = $this->contextualPrice(false);

        if ($this->variants->count() != 0) {
            if (is_null($variant)) {
                /*
                    È rilevante l'ordinamento alfabetico dei valori, soprattutto
                    quando nessuna variante è selezionata di default: essendo
                    preso sempre il primo valore, bisogna accertarsi che il
                    primo sia sempre lo stesso
                */
                $variant = $this->sortedVariantCombos->first();
            }

            if ($variant) {
                $price += $variant->price_offset;
            }
        }

        $currency = currentAbsoluteGas()->currency;
        $str = sprintf('%.02f %s / %s', $price, $currency, $this->printableMeasure());

        return $str;
    }

    /*
        Attenzione: questo non tiene conto dell'eventuale sconto
        applicato sull'ordine in cui il prodotto si trova
    */
    public function getDiscountPriceAttribute()
    {
        if (empty($this->discount)) {
            return $this->price;
        } else {
            return applyPercentage($this->price, $this->discount);
        }
    }

    /*
        Per i prodotti con pezzatura, ritorna già il prezzo per singola unità
        e non è dunque necessario normalizzare ulteriormente
    */
    public function contextualPrice($rectify = true)
    {
        $price = $this->price;

        if ($rectify && $this->portion_quantity != 0) {
            $price = $price * $this->portion_quantity;
        }

        return (float) $price;
    }

    public function printableMeasure($verbose = false)
    {
        if ($this->portion_quantity != 0) {
            if ($verbose) {
                return sprintf('Pezzi da %.02f %s', $this->portion_quantity, $this->measure->name);
            }
            else {
                return sprintf('%.02f %s', $this->portion_quantity, $this->measure->name);
            }
        }
        else {
            $m = $this->measure;
            return $m->name ?? '';
        }
    }

    public function printableDetails($order)
    {
        $details = [];

        $constraints = systemParameters('Constraints');
        foreach($constraints as $constraint) {
            $string = $constraint->printable($this, $order);
            if ($string) {
                $details[] = $string;
            }
        }

        return implode(', ', $details);
    }

    public function hasWarningWithinOrder($summary)
    {
        if (isset($summary->products[$this->id])) {
            $quantity = $summary->products[$this->id]->quantity;

            if ($quantity != 0) {
                $has_warning = $this->package_size != 0 && round(fmod($quantity, $this->fixed_package_size)) != 0;
                if ($has_warning) {
                    return true;
                }

                $has_warning = $this->global_min != 0 && $quantity < $this->global_min;
                if ($has_warning) {
                    return true;
                }
            }
        }

        return false;
    }
}
