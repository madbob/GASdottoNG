<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

use App\Models\Concerns\TracksUpdater;
use App\Models\Concerns\ModifiedTrait;
use App\Models\Concerns\LeafReducibleTrait;
use App\Parameters\Constraints\Constraint;
use App\Exceptions\InvalidQuantityConstraint;
use App\Events\SluggableCreating;

class BookedProduct extends Model
{
    use Cachable, GASModel, HasFactory, LeafReducibleTrait, ModifiedTrait, SluggableID, TracksUpdater;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $touches = ['booking'];

    protected $dispatchesEvents = [
        'creating' => SluggableCreating::class,
    ];

    protected static function boot()
    {
        parent::boot();
        static::initTrackingEvents();
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo('App\Booking');
    }

    public function variants(): HasMany
    {
        return $this->hasMany('App\BookedProductVariant', 'product_id')->with('components');
    }

    public function getStatusAttribute()
    {
        return $this->booking->status;
    }

    /*
        Non viene espressa una relazione con il prodotto di riferimento, ma
        questo viene recuperato direttamente dalla gerarchia cui l'elemento
        appartiene. Questo, sia per motivi di ottimizzazione sia per attingere
        al modello Product che si trova dentro l'ordine e manipolato per
        veicolare con sé il suo prezzo nel contesto dell'ordine stesso (che non
        necessariamente è uguale a quello di un Product recuperato ex-novo dal
        database)
    */
    public function getProductAttribute()
    {
        return $this->booking->order->products->firstWhere('id', $this->product_id);
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
        /*
            Nota: qui si può evitare di ottimizzare controllando che la quantità
            sia diversa da 0, in quanto si può assumere che i prodotti prenotati
            non abbiano mai una quantità a 0
        */
        if ($this->variants->isEmpty() === false) {
            return $this->variants->reduce(function ($carry, $item) use ($rectify, $attribute) {
                return $carry + ($item->unitPrice($rectify) * $item->$attribute);
            }, 0);
        }
        else {
            $content = $this->$attribute;
            if (empty(trim($content))) {
                $content = 0;
            }

            return $this->product->getPrice($rectify) * $content;
        }
    }

    public function testConstraints($quantity, $variant = null, $only_mandatory = false)
    {
        $sorted_contraints = Constraint::sortedContraints($only_mandatory);
        foreach ($sorted_contraints as $constraints) {
            foreach ($constraints as $constraint) {
                $constraint->test($this, $quantity);
            }
        }

        /*
            Può capitare che in una prenotazione ci siano delle varianti che
            solo dopo la chiusura dell'ordine sono state rese non prenotabili.
            In questo caso devo comunque poter consegnare quanto è stato
            prenotato, dunque ignoro lo stato di ordinabilità
        */
        if ($only_mandatory == false) {
            if ($variant) {
                $combo = $variant->variantsCombo();
                if ($combo->active == false) {
                    throw new InvalidQuantityConstraint(_('Questa combinazione di varianti non è attualmente ordinabile'), 4);
                }
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

        foreach ($combo->values as $val) {
            $query->whereHas('components', function ($query) use ($val) {
                $query->where('value_id', $val->id);
            });
        }

        $ret = $query->get();

        foreach ($ret as $r) {
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

        if ($this->variants->isEmpty() === false) {
            $true_variants = [];

            foreach ($this->variants as $variant) {
                $true_variants[] = $variant->reduxData();
            }

            $summary->products[$faked_index]->variants = $true_variants;
        }

        return $summary;
    }

    public function getTrueQuantityAttribute()
    {
        $product = $this->product;
        if ($product->portion_quantity != 0) {
            return $this->quantity * $product->portion_quantity;
        }
        else {
            return $this->quantity;
        }
    }

    /*
        Questa funzione esiste solo come omologo per true_quantity, ma si assume
        che la quantità consegnata sia sempre espressa nel modo corretto
        (ovvero: a peso, in caso di pezzatura)
    */
    public function getTrueDeliveredAttribute()
    {
        return $this->delivered;
    }

    public function basicWeight($attribute)
    {
        $attribute = 'true_' . $attribute;

        return $this->product->weight * $this->$attribute;
    }

    private function fixWeight($attribute)
    {
        if ($this->variants->isEmpty() === false) {
            $ret = $this->variants->reduce(function ($carry, $item) use ($attribute) {
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
            $values = $this->modifiedValues->filter(function ($i) use ($id) {
                return $i->id == $id;
            });
        }

        return $values->reduce(function ($carry, $item) {
            return $carry + $item->effective_amount;
        }, 0);
    }

    private function getPendingValue($type)
    {
        switch ($type) {
            case 'delivered':
                return $this->fixQuantity('delivered', false);

            case 'effective':
                return $this->fixQuantity('quantity', true) + $this->getValue('modifier:all');

            case 'weight':
                return $this->fixWeight('quantity');
        }

        return 0;
    }

    private function getShippedValue($type)
    {
        switch ($type) {
            case 'delivered':
                return $this->final_price;

            case 'effective':
                return $this->final_price + $this->getValue('modifier:all');

            case 'weight':
                return $this->fixWeight('delivered');
        }

        return 0;
    }

    public function getValue($type)
    {
        $ret = 0;

        if (Str::startsWith($type, 'modifier:')) {
            $ret = $this->getModifierValue($type);
        }
        else {
            if ($type == 'booked') {
                $ret = $this->fixQuantity('quantity', true);
            }
            else {
                switch ($this->booking->status) {
                    case 'pending':
                        $ret = $this->getPendingValue($type);
                        break;

                    case 'shipped':
                    case 'saved':
                        $ret = $this->getShippedValue($type);
                        break;
                }
            }
        }

        return $ret;
    }

    public function getFinalUnitPrice()
    {
        if ($this->delivered > 0) {
            return $this->final_price / $this->delivered;
        }
        else {
            return $this->product->getPrice();
        }
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

        $ret->children = function ($item, $filters) {
            return $item->variants;
        };

        $ret->optimize = function ($item, $child) {
            $child->setRelation('product', $item);

            return $child;
        };

        $ret->collected = 'variants';

        return $ret;
    }

    private function initRedux()
    {
        return (object) [
            'id' => $this->product_id,
            'product' => $this->product,
            'variants' => [],
        ];
    }

    public function reduxData($filters = null)
    {
        $ret = $this->initRedux();

        if ($this->variants->isEmpty() === false) {
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

                /*
                    Nota bene: in caso di prodotti con pezzatura, è abbastanza
                    complesso risalire al numero di pezzi effettivi: dividere
                    semplicemente la quantità consegnata (espressa in chili) per
                    la pezzatura produce quasi sempre numeri non interi, che
                    potrebbero essere arrotondati ma non necessariamente
                    corrisponderebbero al numero reale.
                    Pertanto qui desisto dal fare tale calcolo; eventualmente da
                    approfondire qualora ce ne fosse necessità
                */
                'delivered_pieces' => $this->delivered,
            ]);

            $ret = $this->relativeRedux($ret);
        }

        return $ret;
    }
}
