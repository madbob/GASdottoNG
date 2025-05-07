<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

use App\Models\Concerns\ModifiableTrait;
use App\Models\Concerns\Priceable;
use App\Models\Concerns\ProductConcept;
use App\Models\Concerns\TracksUpdater;
use App\Events\VariantChanged;
use App\Events\SluggableCreating;

class Product extends Model
{
    use Cachable, GASModel, HasFactory, ModifiableTrait, Priceable, ProductConcept, SluggableID, SoftDeletes, TracksUpdater;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $dispatchesEvents = [
        'creating' => SluggableCreating::class,
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::initTrackingEvents();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function measure(): BelongsTo
    {
        return $this->belongsTo(Measure::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(Variant::class)->with('values')->orderBy('name', 'asc');
    }

    public function vat_rate(): BelongsTo
    {
        return $this->belongsTo(VatRate::class);
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
        if (empty($this->picture)) {
            return '';
        }
        else {
            return url('products/picture/' . $this->id);
        }
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
        return $this->innerCache('variant_combos', function ($obj) {
            return app('model-cache')->runDisabled(function () use ($obj) {
                $ret = VariantCombo::whereHas('values', function ($query) use ($obj) {
                    $query->whereHas('variant', function ($query) use ($obj) {
                        $query->where('product_id', $obj->id);
                    });
                })->with(['values', 'values.variant'])->get();

                /*
                    Per scrupolo qui faccio un controllo: se il prodotto ha delle
                    varianti ma nessuna combo, ne forzo qui la rigenerazione
                */
                if ($ret->isEmpty() && $this->variants->count() != 0) {
                    foreach ($this->variants as $variant) {
                        VariantChanged::dispatch($variant);
                    }

                    return $this->getVariantCombosAttribute();
                }
                else {
                    /*
                        Una volta ottenuto l'elenco delle combo, setto in modo
                        esplicito la relazione con il prodotto corrente.
                        Questo perché VariantCombo dipende da questa relazione per
                        determinare quale sia il suo stesso prodotto, il quale viene
                        usato sia per formattarne il nome che per determinarne il
                        prezzo (se il prodotto corrente è nel contesto di un ordine)
                    */

                    foreach ($ret as $vc) {
                        foreach ($vc->values as $val) {
                            $val->variant->setRelation('product', $obj);
                        }
                    }

                    return $ret;
                }
            });
        });
    }

    public function getSortedVariantCombosAttribute()
    {
        return $this->variant_combos->where('active', true)->sortBy(function ($combo, $key) {
            return $combo->values->pluck('value')->join(' ');
        }, SORT_NATURAL);
    }

    public function getCategoryNameAttribute()
    {
        $cat = $this->category;
        if ($cat) {
            return $cat->name;
        }
        else {
            return '';
        }
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
        $price = $this->getPrice(false);

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
                $price = $variant->getPrice();
            }
        }

        $currency = defaultCurrency()->symbol;

        /*
            Qui uso sempre il nome assoluto dell'unità di misura, in quanto
            anche per i prodotti con pezzatura il prezzo è sempre riferito al
            chilo
        */
        $str = sprintf('%.02f %s / %s', $price, $currency, $this->measure->name);

        return $str;
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
        foreach ($constraints as $constraint) {
            $string = $constraint->printable($this, $order);
            if ($string) {
                $details[] = $string;
            }
        }

        return implode(', ', $details);
    }

    /*
        Questa funzione determina se posso aggregare le quantità per lo stesso
        prodotto all'interno della stessa prenotazione, in presenza di amici o
        varianti con la stessa combinazione.
        Ci sono casi in cui voglio un unico prodotto prenotato, con una unica
        quantità, e casi in cui per ogni immissione voglio una quantità separata
        (e.g. la carne venduta a pacchi da N etti: può essere sempre la stessa
        carne, ma ne voglio pacchi diversi ciascuno col suo peso)
    */
    public function canAggregateQuantities(): bool
    {
        $hub = App::make('AggregationSwitch');

        if ($hub->isEnforced()) {
            return true;
        }
        else {
            return ($this->portion_quantity == 0 && !$this->measure->discrete && $this->variants->count() != 0) === false;
        }
    }

    public function hasWarningWithinOrder($summary): bool
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

    public function comparePrices($other): bool
    {
        if ($this->getPrice(false) != $other->getPrice(false)) {
            return false;
        }

        foreach ($other->variant_combos as $vc) {
            $ovc = $this->variant_combos->firstWhere('id', $vc->id);
            if ($ovc) {
                if ($ovc->getPrice(false) != $vc->getPrice(false)) {
                    return false;
                }
            }
            else {
                if ($vc->price_offset != 0) {
                    return false;
                }
            }
        }

        return true;
    }

    public static function displayColumns()
    {
        $ret = [];

        $gas = currentAbsoluteGas();
        if ($gas->manual_products_sorting) {
            $ret = [
                'sorting' => (object) [
                    'label' => _i('Ordinamento'),
                    'help' => _i('Ordinamento del prodotto'),
                    'width' => 5,
                ],
            ];
        }

        $ret = $ret + [
            'selection' => (object) [
                'label' => _i('Selezione'),
                'help' => _i('Per selezionare il prodotto e compiere operazioni di gruppo'),
                'width' => 5,
            ],
            'name' => (object) [
                'label' => _i('Nome'),
                'help' => _i('Nome del prodotto'),
                'width' => 20,
            ],
            'category' => (object) [
                'label' => _i('Categoria'),
                'help' => _i('Categoria del prodotto'),
                'width' => 15,
            ],
            'measure' => (object) [
                'label' => _i('Unità di Misura'),
                'help' => _i('Unità di misura del prodotto'),
                'width' => 15,
            ],
            'price' => (object) [
                'label' => _i('Prezzo Unitario'),
                'help' => _i('Prezzo Unitario del prodotto'),
                'width' => 10,
            ],
            'max_available' => (object) [
                'label' => _i('Disponibile'),
                'help' => _i('Quantità massima di prodotto che complessivamente può essere prenotata in un ordine'),
                'width' => 10,
            ],
            'active' => (object) [
                'label' => _i('Ordinabile'),
                'help' => _i("Indica se il prodotto potrà essere ordinato o meno all'interno dei nuovi ordini per il fornitore"),
                'width' => 5,
            ],
        ];

        return $ret;
    }

    /************************************************************** Priceable */

    public function realPrice($rectify)
    {
        $price = $this->price;

        /*
            Qui si legge l'eventuale attributo "prices" che viene recuperato
            accedendo nella relazione products() di un Order
        */
        if (isset($this->pivot->prices)) {
            $prices = json_decode($this->pivot->prices);
            if ($prices) {
                $price = $prices->unit_price;
            }
        }

        if ($rectify && $this->portion_quantity != 0) {
            $price = $price * $this->portion_quantity;
        }

        return (float) $price;
    }

    /********************************************************* ProductConcept */

    public function getConceptID()
    {
        return $this->id;
    }
}
