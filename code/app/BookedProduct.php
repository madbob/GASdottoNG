<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Log;

use App\Events\SluggableCreating;
use App\GASModel;
use App\SluggableID;

class BookedProduct extends Model
{
    use GASModel, SluggableID;

    public $incrementing = false;

    protected $dispatchesEvents = [
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
        /*
            Versioni di MySQL precedente alla 5.7.7 permettono chiavi primarie
            di lunghezza limitata, ma essendo gli ID dei prodotti prenotati una
            combinazione di nome del fornitore, nome del prodotto ed altri
            parametri, possono risultare molto lunghi.
            Pertanto qui, se superano una certa soglia, vengono "accorciati"
            calcolandone l'hash MD5 (almeno della parte meno significativa)
        */
        $string = sprintf('%s::%s', $this->booking->id, $this->product->id);
        if (strlen($string) > 180)
            $string = sprintf('%s::%s', $this->booking->id, md5($this->product->id));
        return $string;
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

                $total += $price * $v->$attribute;
            }

            return $total;
        }
        else {
            if (is_numeric($base_price) == false || is_numeric($this->$attribute) == false)
                Log::error('Non numeric values for booked product: ' . $base_price . ' / ' . $this->$attribute);

            return $base_price * $this->$attribute;
        }
    }

    public function basePrice($rectify = true)
    {
        $product = $this->product;
        return $product->contextualPrice($this->booking->order, $rectify);
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

    /*
        Questa funzione è utile per calcolare dinamicamente il costo del
        prodotto consegnato, il quale viene salvato sul database nell'attributo
        final_price. Leggere tale attributo per ottenere l'informazione, salvata
        e immutabile nel tempo (anche se il prezzo del prodotto di riferimento
        cambia)
    */
    public function deliveredValue()
    {
        return $this->fixQuantity('delivered', false);
    }

    /*
        Valore complessivo di quanto consegnato, diviso tra imponibile e IVA.
        Questa funzione opera sul valore di final_price, dunque solo su prodotti
        che sono già stati effettivamente consegnati
    */
    public function deliveredTaxedValue()
    {
        $product = $this->product;

        $rate = $product->vat_rate;
        if ($rate != null) {
            $total = $this->final_price / (1 + ($rate->percentage / 100));
            $total_vat = $this->final_price - $total;
        }
        else {
            $total = $this->final_price;
            $total_vat = 0;
        }

        return [$total, $total_vat];
    }

    public function transportBookedValue()
    {
        return $this->product->transport * $this->quantity;
    }

    /*
        Questa funzione è utile per calcolare dinamicamente il costo di
        trasporto del prodotto consegnato, il quale viene poi salvato sul
        database nell'attributo final_transport.
        Leggere tale attributo per ottenere l'informazione completa, a sua volta
        eventualmente alterata dal costo di trasporto globale dell'ordine (in
        modo non direttamente correlato al prodotto)
    */
    public function transportDeliveredValue()
    {
        return $this->product->transport * $this->delivered;
    }

    public function getBookedVariant($variant, $fallback = false)
    {
        $v = $this->variants()->where('id', '=', $variant->id)->first();

        if (is_null($v) && $fallback == true) {
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
