<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

use App\Models\Concerns\InCircles;
use App\Models\Concerns\ModifiedTrait;
use App\Models\Concerns\PayableTrait;
use App\Models\Concerns\CreditableTrait;
use App\Models\Concerns\ReducibleTrait;
use App\Models\Concerns\TracksUpdater;
use App\Scopes\RestrictedGAS;
use App\Events\SluggableCreating;
use App\Events\BookingDeleting;

class Booking extends Model
{
    use HasFactory, GASModel, SluggableID, TracksUpdater, InCircles, ModifiedTrait, PayableTrait, CreditableTrait, ReducibleTrait, Cachable;

    public $incrementing = false;
    protected $keyType = 'string';
    public $enforced_total = null;

    protected $dispatchesEvents = [
        'creating' => SluggableCreating::class,
        'deleting' => BookingDeleting::class,
    ];

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
        $this->enableGlobalCache();
    }

    protected static function boot()
    {
        parent::boot();

        static::initTrackingEvents();

        /*
            Questo è per limitare le prenotazioni a quelle effettivamente
            accessibili nel GAS corrente
        */
        static::addGlobalScope('restricted', function(Builder $builder) {
            $builder->whereHas('order', function($query) {
                $query->has('aggregate');
            })->has('user');
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function supplier()
    {
        return $this->order->supplier;
    }

    public function products(): HasMany
    {
        return $this->hasMany(BookedProduct::class)->with(['variants']);
    }

    public function deliverer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deliverer_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Movement::class);
    }

    public function receipts(): BelongsToMany
    {
        return $this->belongsToMany('App\Receipt');
    }

    /*
        Con questo scope si caricano le relazioni utilizzate per il calcolo dei
        modificatori.
        Contro-intuitivamente non ci sono proprio tutte: queste sono state
        identificate in modo empirico come quelle più critiche, variare questo
        elenco magari anche in buona fede può risultare in un impatto negativo
        sulle prestazioni
    */
    public function scopeAngryload($query)
    {
        $query->with([
			'payment', 'modifiedValues', 'modifiedValues.modifier', 'modifiedValues.modifier.modifierType',
            'products', 'products.modifiedValues', 'products.modifiedValues.modifier', 'products.modifiedValues.modifier.modifierType',
            'user', 'user.friends_with_trashed',
            'user.circles', 'user.circles.modifiers', 'user.circles.modifiers.modifierType'
        ]);
    }

    private function localModifiedValues($id)
    {
        $values = $this->modifiedValues;

        if ($id) {
            $values = $values->filter(function($i) use ($id) {
                return $i->modifier_id == $id;
            });
        }

        return $values;
    }

    public function allModifiedValues($id, $with_friends)
    {
        $values = $this->localModifiedValues($id);

        $products = $this->products;
        $values = $products->reduce(function($carry, $product) {
            return $carry->merge($product->modifiedValues);
        }, $values);

        if ($with_friends) {
            foreach($this->friends_bookings as $friend) {
                $values = $values->merge($friend->allModifiedValues($id, true));
            }
        }

        if ($id) {
            $values = $values->filter(function($i) use ($id) {
                return $i->modifier_id == $id;
            });
        }

        return $values;
    }

    /*
        Funzione unica per ottenere i diversi valori della prenotazione: se non
        è ancora stata consegnata calcola al volo i numeri, altrimenti preleva i
        campi salvati sul database al momento della consegna.
    */
    public function getValue($type, $with_friends, $force_recalculate = false)
    {
        $key = sprintf('%s_%s', $type, $with_friends ? 'friends' : 'nofriends');

        if ($force_recalculate) {
            $this->emptyInnerCache($key);
            $this->emptyInnerCache('friends_products');
            $this->unsetRelation('products');
        }

        return $this->innerCache($key, function($obj) use ($type, $with_friends) {
            $value = 0;

            /*
                Il totale di quanto prenotato non cambia a seconda che la
                prenotazione sia consegnata o meno
            */
            if (Str::startsWith($type, 'modifier:')) {
                $id = substr($type, strlen('modifier:'));
                if ($id == 'all') {
                    $id = null;
                }

                $modified_values = $obj->allModifiedValues($id, $with_friends);
                $value = ModifiedValue::sumAmounts($modified_values, 0);
            }
            else {
                if ($with_friends) {
                    $products = $obj->products_with_friends;
                }
                else {
                    $products = $obj->products;
                }

                if ($type == 'effective') {
                    $value = 0;
                    $modified_values = null;

                    /*
                        Se la prenotazione è stata consegnata, devo andare a
                        recuperare i modificatori che sono stati effettivamente
                        salvati sul DB a prescindere da quali sono quelli
                        "teorici" che potrei trovare (quelli restituiti da
                        involvedModifiers()).
                        Questo per recuperare anche gli eventuali modificatori
                        speciali delle consegne manuali
                    */

                    if ($obj->status != 'pending') {
                        $type = 'delivered';
                        $modified_values = $obj->allModifiedValues(null, true);

                        if ($with_friends) {
                            foreach($obj->friends_bookings as $friend_booking) {
                                $friend_modified_values = $friend_booking->allModifiedValues(null, true);
                                $modified_values = $modified_values->merge($friend_modified_values);
                            }
                        }
                    }
                    else {
                        $type = 'booked';
                        $modifiers = $obj->involvedModifiers();

                        if ($modifiers->isEmpty() == false) {
                            $aggregate_data = $obj->minimumRedux($modifiers);
                            $modified_values = $obj->calculateModifiers($aggregate_data, false);

                            if ($with_friends) {
                                foreach($obj->friends_bookings as $friend_booking) {
                                    $friend_modified_values = $friend_booking->calculateModifiers($aggregate_data, false);
                                    $modified_values = $modified_values->merge($friend_modified_values);
                                }
                            }
                        }
                    }

                    if ($modified_values) {
                        $value = ModifiedValue::sumAmounts($modified_values, $value);
                    }
                }

                foreach ($products as $booked) {
                    $booked->setRelation('booking', $obj);
                    $value += $booked->getValue($type);
                }
            }

            return $value;
        });
    }

    public function getBooked($product_id, $fallback = false)
    {
        if (is_object($product_id)) {
            $product = $product_id;
            $product_id = $product_id->id;
        }
        else {
            $product = null;
        }

        $p = $this->products->firstWhere('product_id', $product_id);

        /*
            Se sono in modalità fallback, creo un nuovo oggetto e lo incastro
            nella prenotazione ma senza salvarlo. Verrà poi successivamente
            salvato se e quando sarà necessario (quando sarà accertato che la
            quantità prenotata o consegnata non è 0)
        */
        if (is_null($p) && $fallback == true) {
            $p = new BookedProduct();
            $p->booking_id = $this->id;
            $p->product_id = $product_id;
            $this->products->push($p);
        }

        if (is_null($p) == false) {
            $p->setRelation('booking', $this);

            if ($product) {
                /*
                    In BookingsService recupero eventuali prezzi forzati per i
                    prodotti, e li gestisco con Priceable::setPrice(). Qui devo
                    accertarmi che il medesimo prezzo sia veicolato anche alla
                    nuova relazione (che comunque aggiorno, anche se già
                    caricata, per ogni evenienza)
                */
                if ($p->relationLoaded('product')) {
                    $product->copyPrice($p->product);
                }

                $p->setRelation('product', $product);
            }
        }

        return $p;
    }

    private function readProductQuantity($product, $field, $friends_bookings)
    {
        $combo = null;

        if (is_a($product, VariantCombo::class)) {
            $combo = $product;
            $product = $product->product;
        }

        $p = $this->getBooked($product);
        $ret = 0;

        if ($p) {
            if ($combo) {
                $inner_combos = $p->getBookedCombos($combo);
                foreach($inner_combos as $ic) {
                    $ret += $ic->$field;
                }
            }
            else {
                $ret = $p->$field;
            }
        }

        if ($friends_bookings) {
            foreach ($this->friends_bookings as $sub) {
                $ret += $sub->readProductQuantity($combo ?: $product, $field, false);
			}

        }

        return $ret;
    }

    public function getBookedQuantity($product, $real = false, $friends_bookings = false)
    {
        return $this->readProductQuantity($product, $real ? 'true_quantity' : 'quantity', $friends_bookings);
    }

    /*
        $real: in caso di prodotti con pezzatura, se == false restituisce la
        quantità eventualmente normalizzata in numeri di pezzi altrimenti
        restituisce la quantità intera.
        In caso di prodotti senza pezzatura, restituisce sempre la quantità
        consegnata non ulteriormente elaborata
    */
    public function getDeliveredQuantity($product, $real = false, $friends_bookings = false)
    {
        return $this->readProductQuantity($product, $real ? 'true_delivered' : 'delivered', $friends_bookings);
    }

    /*
        Valore complessivo di quanto ordinato
    */
    public function getValueAttribute()
    {
        return $this->getValue('booked', false);
    }

    /*
        Valore complessivo di quanto consegnato, diviso tra imponibile e IVA
    */
    public function getDeliveredTaxedAttribute()
    {
        $total = 0;
        $total_vat = 0;

        foreach($this->products_with_friends as $booked_product) {
            list($product_total, $product_total_tax) = $booked_product->deliveredTaxedValue();
            $total += $product_total;
            $total_vat += $product_total_tax;
        }

        return [$total, $total_vat];
    }

    public function getProductsWithFriendsAttribute()
    {
        return $this->innerCache('friends_products', function($obj) {
            /*
                Qui devo fare una copia di $this->products anziché usarlo
                direttamente, altrimenti finisco con l'alterare l'elenco stesso
                dei prodotti relazionati alla prenotazione aggiungendoci anche
                quelli degli amici con effetti poco graditi (e.g. in fase di
                gestione della consegna, altero l'entità del prodotto dell'amico
                anziché quello della prenotazione primaria)
            */
            $products = new Collection();
            foreach($this->products as $p) {
                $products->push($p);
            }

            $friends = $this->friends_bookings;

            foreach($friends as $sub) {
                foreach($sub->products as $sub_p) {
                    $master_p = $products->firstWhere('product_id', $sub_p->product_id);

                    if (is_null($master_p)) {
                        $products->push($sub_p);
                    }
                    else {
                        if ($sub_p->product->canAggregateQuantities() == false) {
                            $products->push($sub_p);
                        }
                        else {
                            $master_p->quantity += $sub_p->quantity;
                            $master_p->delivered += $sub_p->delivered;
                            $master_p->final_price += $sub_p->final_price;

                            foreach($sub_p->variants as $sub_variant) {
                                $master_p->variants->squashBookedVariant($sub_variant);
                            }

                            $modifiedValues = $master_p->modifiedValues->merge($sub_p->modifiedValues);
    						$master_p->setRelation('modifiedValues', $modifiedValues);
                        }
                    }
                }
            }

            $products = $products->sort(function($a, $b) {
                return $a->product->name <=> $b->product->name;
            });

            $products = $products->map(function($a) use ($obj) {
                return $a->setRelation('booking', $obj);
            });

            return $products;
        });
    }

    public function getProductsWithFriendsAlwaysAggregatedAttribute()
    {
        $hub = App::make('AggregationSwitch');
        $hub->setEnforced(true);
        $ret = $this->products_with_friends;
        $hub->setEnforced(false);
        return $ret;
    }

    public function getFriendsBookingsAttribute()
    {
        return $this->innerCache('friends_bookings', function($obj) {
            if ($obj->user->friends_with_trashed->isEmpty()) {
                return new Collection();
            }
            else {
                $bookings = Booking::where('order_id', $obj->order_id)->whereIn('user_id', $obj->user->friends_with_trashed->pluck('id'))->angryload()->get();

                foreach($bookings as $b) {
                    $b->setRelation('order', $obj->order);
                }

                return $bookings;
            }
        });
    }

    public function getTotalFriendsValueAttribute()
    {
        $ret = 0;

        foreach($this->friends_bookings as $sub) {
            $ret += $sub->getValue('effective', true);
        }

        return $ret;
    }

    public function involvedCircles()
    {
        if ($this->user->isFriend()) {
            $user_circles = $this->user->parent->circles;
        }
        else {
            $user_circles = $this->user->circles;
        }

        return $this->circles->merge($user_circles);
    }

    public function getCirclesSortingAttribute()
    {
        return $this->involvedCircles()->sortBy('group_id')->map(fn($c) => $c->name)->join(' - ');
    }

    public function printableName()
    {
        return $this->order->printableName();
    }

    public function getShowURL()
    {
        return route('booking.user.show', ['booking' => $this->order->aggregate_id, 'user' => $this->user_id]);
    }

    public function unsetModifiedValues()
    {
        $this->unsetRelation('modifiedValues');

        foreach($this->products as $prod) {
            $prod->unsetRelation('modifiedValues');
        }

        foreach($this->friends_bookings as $friend) {
            $friend->unsetModifiedValues();
        }
    }

    public function wipeStatus()
    {
        if ($this->payment) {
            $this->payment->delete();
        }

        $this->status = 'pending';
        $this->payment_id = null;
        $this->deleteModifiedValues();

        $this->unsetRelation('payment');
        $this->unsetModifiedValues();
        $this->unsetRelation('products');
    }

    public function saveFinalPrices()
    {
        /*
            Qui forzo temporaneamente lo stato della prenotazione per ottenere i
            dati dinamici dai BookedProducts coinvolti
        */

        $keep_status = $this->status;
        $this->status = 'pending';

        foreach($this->products as $p) {
            $p->setRelation('booking', $this);
            $p->final_price = $p->getValue('delivered');
            $p->saveQuietly();
        }

        $this->status = $keep_status;
    }

    public function involvedModifiers()
    {
        $modifiers = $this->order->involvedModifiers(false);

        foreach ($this->involvedCircles() as $circle) {
            $modifiers = $modifiers->merge($circle->modifiers);
        }

        return $modifiers->filter(function($mod) {
            return $mod->active;
        })->sortBy('priority');
    }

    public function involvedModifiedValues()
    {
        $modifiers = $this->modifiedValues;

        foreach($this->products as $product) {
            $modifiers = $modifiers->merge($product->modifiedValues);
        }

        return $modifiers;
    }

    public function saveModifiers($aggregate_data = null)
    {
        /*
            Qui ripulisco i modificatori eventualmente già salvati, nel caso in
            cui la consegna venga modificata e salvata nuovamente
        */
        $this->deleteModifiedValues();

        $this->calculateModifiers($aggregate_data, true);
    }

    /*
        Questa funzione è da usare in caso di Consegne Manuali senza Quantità,
        il valore viene usato in calculateModifiers()
    */
    public function enforceTotal($total)
    {
        $this->enforced_total = $total;
    }

    public function fixPayment()
    {
        $payment = $this->payment;

        if ($payment) {
            $actual_total = $this->getValue('effective', true);

            if ($payment->amount != $actual_total) {
                if ($payment->type_metadata->altersBalances($payment, 'sender')) {
                    /*
                        Questo funziona nella misura in cui la pre-callback per
                        i movimenti di tipo "booking-payment" ignora il valore
                        del movimento passato e ricalcola tutti i valori
                        daccapo, con l'intento di fare tutte le trasformazioni
                        del caso.
                        Pertanto posso anche passargli un valore a 0,
                        l'importante è triggerare una nuova elaborazione per il
                        pagamento di questo aggregato
                    */
                    $mov = Movement::generate('booking-payment', $this->user, $this->order->aggregate, 0);
                }
                else {
                    $mov = Movement::generate('booking-payment-adjust', $this->user, $this, $actual_total - $payment->amount);
                }

                $mov->save();
            }
        }
    }

    public function calculateModifiers($aggregate_data = null, $real = true)
    {
        $values = new Collection();

        $modifiers = $this->involvedModifiers();

        /*
            Se non ci sono modificatori coinvolti, evito di fare la riduzione
            dell'intero aggregato.
        */
        if ($modifiers->isEmpty() == false) {
            if (is_null($aggregate_data)) {
                $aggregate_data = $this->minimumRedux($modifiers);
            }

            /*
                Se il totale della prenotazione viene forzato manualmente, qui
                definisco esplicitamente il suo valore prima dell'elaborazione
                dei modificatori. In questo modo, questi ultimi saranno
                calcolati in base al totale manuale anziché quello teorico
            */
            if ($this->enforced_total) {
                $aggregate_data->orders[$this->order_id]->bookings[$this->id]->price_delivered = $this->enforced_total;
            }

            if ($real == false) {
                DB::beginTransaction();
            }

            $engine = app()->make('ModifierEngine');

            foreach($modifiers as $modifier) {
                $value = $engine->apply($modifier, $this, $aggregate_data);
                if ($value) {
                    $values = $values->push($value);
                }
            }

            if ($real == false) {
                DB::rollback();
            }
        }

        return $values;
    }

    public function applyModifiers($aggregate_data = null, $real = true)
    {
        if ($this->status != 'pending') {
            return $this->allModifiedValues(null, true);
        }
        else {
            return $this->calculateModifiers($aggregate_data, $real);
        }
    }

    public function aggregatedModifiers()
    {
        $modifiers = $this->applyModifiers(null, false);
        return ModifiedValue::aggregateByType($modifiers);
    }

    public function deleteModifiedValues()
    {
        $modified = $this->involvedModifiedValues();
        foreach($modified as $mod) {
            $mod->delete();
        }
    }

    /********************************************************** ModifiedTrait */

    public function getModifiedRelations()
    {
        return (object) [
            'supplier' => $this->order->supplier,
            'user' => $this->user,
        ];
    }

    /********************************************************* ReducibleTrait */

    protected function reduxBehaviour()
    {
        $ret = $this->emptyReduxBehaviour();

        $ret->children = function($item, $filters) {
            return $item->products;
        };

        $ret->optimize = function($item, $child) {
            $child->setRelation('booking', $item);
            $child->setRelation('product', $item->order->products->firstWhere('id', $child->product_id));
            return $child;
        };

        $ret->collected = 'products';
        return $ret;
    }

    /************************************************************ SluggableID */

    public function getSlugID()
    {
        return sprintf('%s::%s', $this->order->id, $this->user->id);
    }

    /******************************************************** CreditableTrait */

    public function getBalanceProxy()
    {
        return $this->order->supplier;
    }

    public function balanceFields()
    {
        return [
            'bank' => _i('Saldo'),
        ];
    }

    public static function commonClassName()
    {
        return 'Prenotazione';
    }

    /************************************************************** InCircles */

    public function eligibleGroups()
    {
        return Group::whereIn('id', $this->order->circles()->pluck('group_id')->unique()->toArray())->where('context', 'booking')->orderBy('name', 'asc')->get();
    }
}
