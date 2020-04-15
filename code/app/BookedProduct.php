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
            if (is_numeric($base_price) == false || is_numeric($this->$attribute) == false) {
                Log::error('Non numeric values for booked product: ' . $base_price . ' / ' . $this->$attribute);
            }

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
        $net_final_price = $this->final_price - $this->final_discount;

        $rate = $product->vat_rate;
        if ($rate != null) {
            $total = $net_final_price / (1 + ($rate->percentage / 100));
            $total_vat = $net_final_price - $total;
        }
        else {
            $total = $net_final_price;
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

    public function discountDeliveredValue()
    {
        if (empty($this->product->discount)) {
            return 0;
        }

        if (isPercentage($this->product->discount)) {
            return applyPercentage($this->final_price, $this->product->discount, '=');
        }
        else {
            return $this->product->discount * $this->delivered;
        }
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

    private function dynamicTransportCost()
    {
        $global_transport = $this->booking->dynamicTransportCost(true, false);
        $booking_value = $this->booking->getValue('booked', true);

        if ($booking_value != 0)
            $per_product = round(($global_transport * $this->quantityValue()) / $booking_value, 2);
        else
            $per_product = 0;

        return $this->transportBookedValue() + $per_product;
    }

    /*
        Questa funzione serve a generare un oggetto simile a quello prodotto da
        Order::calculateSummary() ma relativo solo a questo prodotto.
        Usato per essere dato in input alle callback di formattazione di
        Order::formattableColumns()
    */
    public function getAsSummaryAttribute()
    {
        $faked_index = $this->product->id;

        $summary = (object) [
            'products' => [
                $faked_index => [
                    'product_obj' => $this->product,
                    'quantity' => $this->quantity,
                    'quantity_pieces' => $this->product->portion_quantity > 0 ? $this->quantity * $this->product->portion_quantity : $this->quantity,
                    'price' => $this->quantityValue(),
                    'transport' => $this->dynamicTransportCost(),
                    'delivered' => $this->delivered,
                    'delivered_pieces' => $this->product->portion_quantity > 0 ? $this->delivered * $this->product->portion_quantity : $this->delivered,
                    'price_delivered' => $this->deliveredValue(),
                    'transport_delivered' => $this->final_transport,
                ]
            ],
            'by_variant' => []
        ];

        $variants_quantity = 0;

        foreach($this->variants as $v) {
            $name = $v->printableName();
            if(isset($summary->by_variant[$faked_index][$name]) == false) {
                $summary->by_variant[$faked_index][$name] = [
                    'quantity' => 0,
                    'delivered' => 0,
                    'price' => 0,
                    'unit_price' => $v->unitPrice()
                ];
            }

            $summary->by_variant[$faked_index][$name]['quantity'] += $v->quantity;
            $summary->by_variant[$faked_index][$name]['delivered'] += $v->delivered;
            $summary->by_variant[$faked_index][$name]['price'] += $v->quantityValue();

            $variants_quantity += $v->quantity;
        }

        if ($variants_quantity != 0)
            $summary->products[$faked_index]['quantity'] = $variants_quantity;

        return $summary;
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

    public function quantityWeight()
    {
        if ($this->product->measure->discrete) {
            return $this->product->weight * $this->quantity;
        }
        else {
            return $this->true_quantity;
        }
    }

    public function deliveredWeight()
    {
        if ($this->product->measure->discrete) {
            return $this->product->weight * $this->delivered;
        }
        else {
            return $this->true_delivered;
        }
    }
}
