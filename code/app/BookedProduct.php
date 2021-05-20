<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Str;

use Log;

use App\Events\SluggableCreating;

class BookedProduct extends Model
{
    use GASModel, SluggableID, ModifiedTrait, ReducibleTrait;

    public $incrementing = false;
    protected $keyType = 'string';

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
        if ($this->variants->isEmpty() == false) {
            $total = 0;

            foreach ($this->variants as $v) {
                $total += $v->unitPrice($rectify) * $v->$attribute;
            }

            return $total;
        }
        else {
            /*
                Per i prodotti con pezzatura, basePrice() già fornisce il prezzo per
                singola unità. Non è dunque qui necessario effettuare altri
                controlli o aggiustamenti
            */
            $base_price = $this->basePrice($rectify);

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

    public function testConstraints($quantity)
    {
        $product = $this->product;

        if ($product->min_quantity != 0) {
            if ($quantity < $product->min_quantity) {
                return 0;
            }
        }

        if ($product->multiple != 0) {
            if ($quantity % $product->multiple != 0) {
                return 0;
            }
        }

        if ($product->max_available != 0) {
            if ($quantity > ($product->stillAvailable($this->booking->order) + $this->quantity)) {
                return 0;
            }
        }

        return $quantity;
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
                $faked_index => $this->reduxData(),
            ],
        ];

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
        if ($this->variants->isEmpty() == false) {
            $total = 0;

            foreach ($this->variants as $v) {
                $total += $v->fixWeight($attribute);
            }

            return $total;
        }
        else {
            return $this->product->weight * $this->$attribute;
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
                                return $this->fixQuantity('delivered', true);
                                break;

                            case 'effective':
                                return $this->fixQuantity('quantity', true) + $this->getValue('modifier:all');
                                break;

                            case 'weight':
                                if ($this->product->measure->discrete) {
                                    return $this->fixWeight('quantity');
                                }
                                else {
                                    return $this->fixWeight('true_quantity');
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
                                    return $this->fixWeight('true_delivered');
                                }

                                break;
                        }

                        break;
                }
            }
        }

        return 0;
    }

    /********************************************************** ModifiedTrait */

    public function getModifiedRelations()
    {
        return (object) [
            'supplier' => $this->booking->order->supplier,
            'user' => $this->booking->user,
        ];
    }

    /********************************************************* ReducibleTrait */

    protected function reduxBehaviour()
    {
        $ret = $this->emptyReduxBehaviour();

        $ret->children = function($item, $filters) {
            return $this->variants;
        };

        $ret->optimize = function($item, $child) {
            $child->setRelation('product', $item);
            return $child;
        };

        $ret->collected = 'variants';
        return $ret;
    }

    public function reduxData($ret = null, $filters = null)
    {
        if (is_null($ret)) {
            $ret = (object) [
                'id' => $this->product_id,
                'product' => $this->product,
                'variants' => [],
            ];
        }

        if ($this->variants->isEmpty() == false) {
            $ret = $this->descendReduction($ret, $filters);
        }
        else {
            $ret = $this->describingAttributesMerge($ret, (object) [
                'price' => $this->getValue('booked'),
                'weight' => $this->fixWeight('quantity'),
                'quantity' => $this->quantity,
                'quantity_pieces' => $this->product->portion_quantity > 0 ? $this->quantity * $this->product->portion_quantity : $this->quantity,
                'price_delivered' => $this->getValue('delivered'),
                'weight_delivered' => $this->fixWeight('delivered'),
                'delivered' => $this->product->portion_quantity > 0 ? $this->delivered * $this->product->portion_quantity : $this->delivered,
                'delivered_pieces' => $this->delivered,
            ]);
        }

        return $ret;
    }
}
