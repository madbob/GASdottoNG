<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Events\SluggableCreating;
use App\GASModel;
use App\SluggableID;
use App\Booking;
use App\BookedProduct;

class Product extends Model
{
    use SoftDeletes, GASModel, SluggableID;

    public $incrementing = false;

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

    public function getSlugID()
    {
        return sprintf('%s::%s', $this->supplier_id, str_slug($this->name));
    }

    public function getPictureUrlAttribute()
    {
        if (empty($this->picture))
            return '';
        else
            return url('products/picture/' . $this->id);
    }

    public function stillAvailable($order)
    {
        if ($this->max_available == 0) {
            return 0;
        }

        $quantity = BookedProduct::where('product_id', '=', $this->id)->whereHas('booking', function ($query) use ($order) {
            $query->where('order_id', '=', $order->id);
        })->sum('quantity');

        if ($this->portion_quantity != 0)
            $quantity *= $this->portion_quantity;

        return $this->max_available - $quantity;
    }

    public function bookingsInOrder($order)
    {
        $id = $this->id;

        return Booking::where('order_id', '=', $order->id)->whereHas('products', function ($query) use ($id) {
            $query->where('product_id', '=', $id);
        })->get();
    }

    public function printablePrice($order)
    {
        $price = $this->contextualPrice($order, false);
        $currency = currentAbsoluteGas()->currency;

        if (!empty($this->transport) && $this->transport != 0) {
            $str = sprintf('%.02f %s / %s + %.02f %s trasporto', $price, $currency, $this->measure->name, $this->transport, $currency);
        } else {
            $str = sprintf('%.02f %s / %s', $price, $currency, $this->measure->name);
        }

        if ($this->variable) {
            $str .= '<small> <span class="visible-sm">' . _i('(prodotto a prezzo variabile)') . '</span><span class="visible-xs">' . _i('(variabile)') . '</span></small>';
        }

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
        Questo è per determinare il prezzo del prodotto in un dato contesto,
        ovvero in un ordine. Se lo sconto del singolo prodotto è stato abilitato
        per l'ordine, viene applicato. Altrimenti resta il prezzo di
        riferimento.
        Per i prodotti con pezzatura, ritorna già il prezzo per singola unità
        e non è dunque necessario normalizzare ulteriormente
    */
    public function contextualPrice($order, $rectify = true)
    {
        /*
            Attenzione: hasProduct() altera il riferimento a $product,
            popolandolo con i dati pescati dal database in relazione all'ordine
            desiderato.
            Ma $this potrebbe non essere una copia genuina del prodotto, in
            quanto i suoi parametri possono essere temporaneamente sovrascritti
            (cfr. OrderController::recalculate), e non si vuole che essi siano
            riletti dal database.
            Sicché, a hasProduct passo una copia e preservo l'originale
        */
        $product = $this->replicate();
        $enabled = $order->hasProduct($product);

        if ($enabled && $product->pivot->discount_enabled) {
            $price = applyPercentage($this->price, $this->discount);
        } else {
            $price = $this->price;
        }

        if ($rectify && $product->portion_quantity != 0)
            $price = $price * $product->portion_quantity;

        return $price;
    }

    public function printableMeasure($verbose = false)
    {
        if ($this->portion_quantity != 0) {
            if ($verbose)
                return sprintf('Pezzi da %.02f %s', $this->portion_quantity, $this->measure->name);
            else
                return sprintf('%.02f %s', $this->portion_quantity, $this->measure->name);
        }
        else {
            $m = $this->measure;
            if (is_null($m)) {
                return '';
            } else {
                return $m->name;
            }
        }
    }

    public function printableDetails($order)
    {
        $details = [];

        if ($this->min_quantity != 0) {
            $details[] = _i('Minimo: %.02f', $this->min_quantity);
        }
        if ($this->max_quantity != 0) {
            $details[] = _i('Massimo Consigliato: %.02f', $this->max_quantity);
        }
        if ($this->max_available != 0) {
            $details[] = _i('Disponibile: %.02f (%.02f totale)', [$this->stillAvailable($order), $this->max_available]);
        }
        if ($this->multiple != 0) {
            $details[] = _i('Multiplo: %.02f', $this->multiple);
        }

        return implode(', ', $details);
    }

    public function variantsCombinations()
    {
        $combinations = [];

        foreach($this->variants as $variant) {
            $offset = 0;
            $same_price = [];
            $sp_index = 0;

            foreach($variant->values as $value) {
                if (!isset($same_price[(string)$value->price_offset])) {
                    $same_price[(string)$value->price_offset] = (object) [
                        'name' => [],
                        'price' => $this->price + $value->price_offset
                    ];
                }

                $same_price[(string)$value->price_offset]->name[] = $value->value;
            }

            if (empty($combinations)) {
                foreach($same_price as $sp) {
                    $combinations[] = (object) [
                        'name' => join(', ', $sp->name),
                        'price' => $sp->price
                    ];
                }
            }
            else {
                foreach($combinations as $index => $n) {
                    foreach($same_price as $price_offset => $sp) {
                        $combinations[$index]->name = sprintf('%s / %s', $combinations[$index]->name, join(', ', $sp->name));
                        $combinations[$index]->price += (float) $price_offset;
                    }
                }
            }
        }

        return $combinations;
    }
}
