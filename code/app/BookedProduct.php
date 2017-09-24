<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use App\Events\SluggableCreating;
use App\GASModel;
use App\SluggableID;

class BookedProduct extends Model
{
    use GASModel, SluggableID;

    public $incrementing = false;

    protected $events = [
        'creating' => SluggableCreating::class,
    ];

    public function product()
    {
        return $this->belongsTo('App\Product')->withTrashed();
    }

    public function booking()
    {
        return $this->belongsTo('App\Booking');
    }

    public function variants()
    {
        return $this->hasMany('App\BookedProductVariant', 'product_id')->with('components');
    }

    public function getSlugID()
    {
        return sprintf('%s::%s', $this->booking->id, $this->product->id);
    }

    private function fixQuantity($attribute, $rectify)
    {
        /*
            Per i prodotti con pezzatura, basePrice() già fornisce il prezzo per
            singola unità. Non è dunque qui necessario effettuare altri
            controlli o aggiustamenti
        */
        $base_price = $this->basePrice($rectify);
        $product = $this->product;

        $variants = $this->variants;
        if ($variants->isEmpty() == false) {
            $total = 0;

            foreach ($variants as $v) {
                $price = $base_price;

                foreach ($v->components as $c) {
                    $price += $c->value->price_offset;
                }

                $total += ($price + $product->transport) * $v->$attribute;
            }

            return $total;
        }
        else {
            return ($base_price + $product->transport) * $this->$attribute;
        }
    }

    public function basePrice($rectify = true)
    {
        $product = $this->product;
        return $product->contextualPrice($this->booking->order, $rectify) + $product->transport;
    }

    /*
        In caso di prodotti con pezzatura, si prenota per pezzi e si consegna
        per quantità.
        E.g. prodotto distribuito in chili con pezzetura 0.4, ne ordino 2 (pezzi
        da 0.4 chili) e ne consegno 0.8 (chili complessivi).
        Dunque anche il calcolo dei valori deve tener presente del diverso
        significato delle colonne quantity e delivered, premesso che il prezzo è
        sempre espresso nell'unità di misura principale (nel caso di cui sopra:
        prezzo al chilo)
    */

    public function quantityValue()
    {
        return $this->fixQuantity('quantity', true);
    }

    public function deliveredValue()
    {
        return $this->fixQuantity('delivered', false);
    }

    public function getBookedVariant($variant, $fallback = false)
    {
        $v = $this->variants()->where('id', '=', $variant->id)->first();

        if ($v == null && $fallback == true) {
            $v = new BookedProductVariant();
            $v->product_id = $this->id;
        }

        return $v;
    }

    private function normalizeQuantity($attribute)
    {
        $product = $this->product;
        if ($product->portion_quantity != 0)
            return $this->$attribute * $product->portion_quantity;
        else
            return $this->$attribute;
    }

    public function getTrueQuantityAttribute()
    {
        return $this->normalizeQuantity('quantity');
    }

    public function getTrueDeliveredAttribute()
    {
        return $this->normalizeQuantity('delivered');
    }
}
