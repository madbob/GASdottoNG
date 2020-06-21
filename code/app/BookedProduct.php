<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Str;

use Log;

use App\Events\SluggableCreating;

class BookedProduct extends Model
{
    use GASModel, SluggableID, ModifiedTrait;

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
        return $this->product->contextualPrice($this->booking->order, $rectify);
    }

    /*
        Valore complessivo di quanto consegnato, diviso tra imponibile e IVA.
        Questa funzione opera sul valore di final_price, dunque solo su prodotti
        che sono già stati effettivamente consegnati
    */
    public function deliveredTaxedValue()
    {
        $product = $this->product;
        $net_final_price = $this->getValue('effective');

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

    public function getBookedVariant($variant, $fallback = false)
    {
        $v = $this->variants()->where('id', '=', $variant->id)->first();

        if (is_null($v) && $fallback == true) {
            $v = new BookedProductVariant();
            $v->product_id = $this->id;
        }

        return $v;
    }

    /*
        Questa funzione serve a generare un oggetto simile a quello prodotto da
        Order::calculateSummary() ma relativo solo a questo prodotto.
        In particolare, viene usato per formattare il contenuto del Dettaglio
        Consegne (in cui ogni prodotto va gestito singolarmente, non aggregato).
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
                    'quantity' => $this->product->portion_quantity > 0 ? $this->quantity * $this->product->portion_quantity : $this->quantity,
                    'quantity_pieces' => $this->quantity,
                    'price' => $this->getValue('booked'),
                    'delivered' => $this->delivered,
                    'delivered_pieces' => $this->product->portion_quantity > 0 ? $this->delivered * $this->product->portion_quantity : $this->delivered,
                    'price_delivered' => $this->getValue('delivered'),
                ]
            ],
            'by_variant' => []
        ];

        if ($this->variants->isEmpty() == false) {
            $variants_quantity = 0;
            $summary->by_variant[$faked_index] = [];

            foreach($this->variants as $v) {
                $name = $v->printableName();
                $variant_index = -1;

                /*
                    Per i prodotti con unità di misura non discreta gestisco le
                    diverse varianti separatamente, per distinguere i singoli
                    pezzi prenotati
                */
                if ($this->product->measure->discrete == true) {
                    foreach($summary->by_variant[$faked_index] as $vindex => $var_iter) {
                        if ($var_iter['name'] == $name) {
                            $variant_index = $vindex;
                            break;
                        }
                    }
                }

                if ($variant_index == -1) {
                    $variant_index = count($summary->by_variant[$faked_index]);
                    $summary->by_variant[$faked_index][$variant_index] = [
                        'name' => $name,
                        'quantity' => 0,
                        'delivered' => 0,
                        'price' => 0,
                        'transport' => 0,
                        'unit_price' => $v->unitPrice()
                    ];
                }

                $summary->by_variant[$faked_index][$variant_index]['quantity'] += $v->quantity;
                $summary->by_variant[$faked_index][$variant_index]['delivered'] += $v->delivered;
                $summary->by_variant[$faked_index][$variant_index]['price'] += $v->quantityValue();

                $relative_transport = ($v->quantityValue() * $relative_transport = $summary->products[$faked_index]['transport']) / $summary->products[$faked_index]['price'];
                $summary->by_variant[$faked_index][$variant_index]['transport'] += $relative_transport;

                $variants_quantity += $v->quantity;
            }

            if ($variants_quantity != 0) {
                $summary->products[$faked_index]['quantity'] = $variants_quantity;
            }
        }

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

    private function fixWeight($attribute)
    {
        $weight = $this->product->weight;

        $variants = $this->variants;
        if ($variants->isEmpty() == false) {
            $total = 0;

            foreach ($variants as $v) {
                foreach ($v->components as $c) {
                    $weight += $c->value->weight_offset;
                }

                $total += $weight * $v->$attribute;
            }

            return $total;
        }
        else {
            return $weight * $this->$attribute;
        }
    }

    public function getValue($type)
    {
        if (Str::startsWith($type, 'modifier:')) {
            $id = substr($type, strlen('modifier:'));
            if ($id == 'all') {
                $values = $this->modifiedValues;
            }
            else {
                $values = $this->modifiedValues->filter(function($i) use ($id) {
                    return $i->id == $id;
                });
            }

            return $values->reduce(function($carry, $item) {
                return $carry + $item->effective_amount;
            }, 0);
        }
        else {
            if ($type == 'booked') {
                return $this->fixQuantity('quantity', true);
            }
            else {
                switch($this->booking->status) {
                    case 'pending':
                        switch($type) {
                            case 'delivered':
                                return 0;
                                break;

                            case 'effective':
                                return $this->fixQuantity('quantity', true) + $this->getValue('modifier:all');
                                break;

                            case 'weight':
                                if ($this->product->measure->discrete) {
                                    return $this->fixWeight('quantity');
                                }
                                else {
                                    return $this->true_quantity;
                                }

                                break;
                        }

                        break;

                    case 'shipped':
                    case 'saved':
                        switch($type) {
                            case 'delivered':
                                return $this->final_price;
                                break;

                            case 'effective':
                                return $this->fixQuantity('delivered', true) + $this->getValue('modifier:all');
                                break;

                            case 'weight':
                                if ($this->product->measure->discrete) {
                                    return $this->fixWeight('delivered');
                                }
                                else {
                                    return $this->true_delivered;
                                }

                                break;
                        }

                        break;
                }
            }
        }

        return 0;
    }

    public function reduxData()
    {
        return (object) [
            'product_id' => $this->product_id,

            /*
                Questi attributi devono essere coerenti con quelli descritti in
                Refelction::describingAttributes()
            */

            'price' => $this->getValue('booked'),
            'weight' => $this->fixWeight('quantity'),
            'quantity' => $this->product->portion_quantity > 0 ? $this->quantity * $this->product->portion_quantity : $this->quantity,
            'quantity_pieces' => $this->quantity,

            'price_delivered' => $this->getValue('delivered'),
            'weight_delivered' => $this->fixWeight('delivered'),
            'delivered' => $this->product->portion_quantity > 0 ? $this->delivered * $this->product->portion_quantity : $this->delivered,
            'delivered_pieces' => $this->delivered,
        ];
    }
}
