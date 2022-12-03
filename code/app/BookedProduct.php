<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

use Log;

use App\Exceptions\InvalidQuantityConstraint;
use App\Events\SluggableCreating;

class BookedProduct extends Model
{
    use HasFactory, GASModel, SluggableID, ModifiedTrait, LeafReducibleTrait, Cachable;

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

    public function getStatusAttribute()
    {
        return $this->booking->status;
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

        if (strlen($string) > 180) {
            $string = sprintf('%s::%s', $this->booking->id, md5($this->product->id));
        }

        return $string;
    }

    private function fixQuantity($attribute, $rectify)
    {
        if ($this->variants->isEmpty() == false) {
            return $this->variants->reduce(function($carry, $item) use ($rectify, $attribute) {
                return $carry + ($item->unitPrice($rectify) * $item->$attribute);
            }, 0);
        }
        else {
            /*
                Per i prodotti con pezzatura, basePrice() già fornisce il prezzo per
                singola unità. Non è dunque qui necessario effettuare altri
                controlli o aggiustamenti
            */
            $base_price = $this->basePrice($rectify);

            $content = $this->$attribute;
            if (empty(trim($content))) {
                $content = 0;
            }

            return $base_price * $content;
        }
    }

    public function basePrice($rectify = true)
    {
        return $this->product->contextualPrice($rectify);
    }

    public function testConstraints($quantity, $variant = null)
    {
        $sorted_contraints = \App\Parameters\Constraints\Constraint::sortedContraints();
        foreach($sorted_contraints as $constraints) {
            foreach($constraints as $constraint) {
                $constraint->test($this, $quantity);
            }
        }

        if ($variant) {
            $combo = $variant->variantsCombo();
            if ($combo->active == false) {
                throw new InvalidQuantityConstraint(_('Questa combinazione di varianti non è attualmente ordinabile'), 4);
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

    public function getBookedCombos($combo)
    {
        $query = $this->variants();

        foreach($combo->values as $val) {
            $query->whereHas('components', function($query) use ($val) {
                $query->where('value_id', $val->id);
            });
        }

        $ret = $query->get();

        foreach($ret as $r) {
            $r->setRelation('product', $this);
        }

        return $ret;
    }

    /*
        Questa funzione serve a generare un oggetto simile a quello prodotto da
        Order::calculateSummary() ma relativo solo a questo prodotto.
        In particolare, viene usato per formattare il contenuto del Dettaglio
        Consegne, in cui ogni prodotto (e più in particolare ogni variante del
        prodotto) va gestito singolarmente e non aggregato.
        Da non usare in altri casi!!!
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

        if ($this->variants->isEmpty() == false) {
            $true_variants = [];

            foreach($this->variants as $variant) {
                $true_variants[] = $variant->reduxData();
            }

            $summary->products[$faked_index]->variants = $true_variants;
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

    /*
        Questa funzione è intesa solo per essere invocata da
        BookedProductVariant per ottenere il peso di base del prodotto, su cui
        applicare le differenze peso. Evitare di usarla altrove
    */
    public function basicWeight($attribute)
    {
        if ($this->product->measure->discrete == false) {
            $measure_weight = $this->product->measure->weight;
            if ($measure_weight == 0) {
                $measure_weight = 1;
            }

            if ($this->product->portion_quantity > 0) {
                $ret = $this->product->portion_quantity * $this->$attribute;
            }
            else {
                $ret = $this->$attribute;
            }

            $ret = $ret * $measure_weight;
        }
        else {
            $ret = $this->product->weight * $this->$attribute;
        }

        return $ret;
    }

    private function fixWeight($attribute)
    {
        if ($this->variants->isEmpty() == false) {
            $ret = $this->variants->reduce(function($carry, $item) use ($attribute) {
                return $carry + $item->fixWeight($attribute);
            }, 0);
        }
        else {
            $ret = $this->basicWeight($attribute);
        }

        return $ret;
    }

    private function getModifierValue($type)
    {
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

    private function getPendingValue($type)
    {
        switch($type) {
            case 'delivered':
                return $this->fixQuantity('delivered', false);

            case 'effective':
                return $this->fixQuantity('quantity', true) + $this->getValue('modifier:all');

            case 'weight':
                if ($this->product->measure->discrete) {
                    return $this->fixWeight('quantity');
                }
                else {
                    return $this->fixWeight('true_quantity');
                }
        }

        return 0;
    }

    private function getShippedValue($type)
    {
        switch($type) {
            case 'delivered':
                return $this->final_price;

            case 'effective':
                return $this->final_price + $this->getValue('modifier:all');

            case 'weight':
                if ($this->product->measure->discrete) {
                    return $this->fixWeight('delivered');
                }
                else {
                    return $this->fixWeight('true_delivered');
                }
        }

        return 0;
    }

    public function getValue($type)
    {
        if (Str::startsWith($type, 'modifier:')) {
            return $this->getModifierValue($type);
        }
        else {
            if ($type == 'booked') {
                return $this->fixQuantity('quantity', true);
            }
            else {
                switch($this->booking->status) {
                    case 'pending':
                        return $this->getPendingValue($type);

                    case 'shipped':
                    case 'saved':
                        return $this->getShippedValue($type);
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

    private function initRedux($ret)
    {
        if (is_null($ret)) {
            $ret = (object) [
                'id' => $this->product_id,
                'product' => $this->product,
                'variants' => [],
            ];
        }

        return $ret;
    }

    public function reduxData($ret = null, $filters = null)
    {
        $ret = $this->initRedux($ret);

        if ($this->variants->isEmpty() == false) {
            $ret = $this->descendReduction($ret, $filters);
        }
        else {
            $ret = $this->describingAttributesMerge($ret, (object) [
                'price' => $this->getValue('booked'),
                'weight' => $this->fixWeight('quantity'),
                'quantity' => $this->product->portion_quantity > 0 ? $this->quantity * $this->product->portion_quantity : $this->quantity,
                'quantity_pieces' => $this->quantity,
                'price_delivered' => $this->getValue('delivered'),
                'weight_delivered' => $this->fixWeight('delivered'),
                'delivered' => $this->delivered,
                'delivered_pieces' => $this->product->portion_quantity > 0 ? $this->delivered / $this->product->portion_quantity : $this->delivered,
            ]);

            $ret = $this->relativeRedux($ret);
        }

        return $ret;
    }
}
