<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Carbon\Carbon;

use App\Models\Concerns\InCircles;
use App\Models\Concerns\AttachableTrait;
use App\Models\Concerns\PayableTrait;
use App\Models\Concerns\CreditableTrait;
use App\Models\Concerns\ModifiableTrait;
use App\Models\Concerns\ExportableTrait;
use App\Models\Concerns\ReducibleTrait;
use App\Models\Concerns\TracksUpdater;
use App\Scopes\RestrictedGAS;
use App\Events\SluggableCreating;

class Order extends Model
{
    use AttachableTrait, CreditableTrait, ExportableTrait, GASModel, HasFactory, InCircles, ModifiableTrait, PayableTrait, ReducibleTrait, SluggableID, TracksUpdater;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $dispatchesEvents = [
        'creating' => SluggableCreating::class,
    ];

    protected static function boot()
    {
        parent::boot();
        static::initTrackingEvents();
        static::addGlobalScope(new RestrictedGAS('aggregate.gas'));
    }

    public static function commonClassName()
    {
        return _i('Ordine');
    }

    public function supplier(): BelongsTo
    {
        /*
            La rimozione dello scope globale serve nel caso del Multi-GAS, per
            accedere al fornitore di un ordine anche se il fornitore stesso non
            è visibile al GAS
        */
        return $this->belongsTo('App\Supplier')->withoutGlobalScopes()->withTrashed();
    }

    public function aggregate(): BelongsTo
    {
        return $this->belongsTo('App\Aggregate');
    }

    public function products(): BelongsToMany
    {
        /*
            Nota bene: è importante che i prodotti dall'ordine siano caricati
            con il relativo valore di "prices" dalla tabella pivot, le funzioni
            di accesso al prezzo (cfr. Priceable) dipendono dall'esistenza di
            questo attributo nell'oggetto
        */
        return $this->belongsToMany('App\Product')->with(['measure', 'variants', 'modifiers'])->withPivot(['notes', 'prices'])->withTrashed();
    }

    public function bookings(): HasMany
    {
        return $this->hasMany('App\Booking')->with(['user', 'products']);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo('App\Movement');
    }

    public function invoice(): BelongsToMany
    {
        return $this->belongsToMany('App\Invoice');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany('App\User');
    }

    public function printableName()
    {
        $ret = $this->supplier->name;

        if (! empty($this->comment) && strlen($this->comment) < longCommentLimit()) {
            $ret .= ' - ' . $this->comment;
        }

        $ret .= ' - ' . $this->internal_number;

        return $ret;
    }

    public function statusIcons()
    {
        $icons = $this->icons('status');

        return $this->formatIcons($icons);
    }

    /*
        Per filtrare gli ordini in funzione dei Circle di appartenenza.
        Si applica solo le l'ordine è effettivamente assegnato a dei Circle il
        cui contesto del Group è "user". non per gli altri contesti
    */
    public function scopeAccessibleBooking($query)
    {
        $user = Auth::user();

        if ($user) {
            $query->where(function ($query) use ($user) {
                $query->whereDoesntHave('circles', function ($query) {
                    $query->whereHas('group', function ($q) {
                        $q->where('context', 'user');
                    });
                })->orWhereHas('circles', function ($query) use ($user) {
                    if ($user->isFriend()) {
                        $circles = $user->parent->circles->pluck('id');
                    }
                    else {
                        $circles = $user->circles->pluck('id');
                    }

                    $query->whereHas('group', function ($q) {
                        $q->where('context', 'user');
                    })->whereIn('circles.id', $circles);
                });
            });
        }
    }

    /*
        A differenza di getProductsAttribute, questa funzione ritorna una
        Collection che appiana prodotti e combo coinvolti nell'ordine. Questo
        per semplificare l'iterazione di tali entità, che sono comunque spesso
        trattate come prodotti indipendenti
    */
    public function getProductConceptsAttribute()
    {
        $ret = new Collection();

        foreach ($this->products as $product) {
            if ($product->variants->isEmpty()) {
                $ret->push($product);
            }
            else {
                foreach ($product->variant_combos as $combo) {
                    $ret->push($combo);
                }
            }
        }

        return $ret;
    }

    public function printableDates()
    {
        $start = strtotime($this->start);
        $end = strtotime($this->end);
        $string = _i('da %s a %s', [printableDate($start), printableDate($end)]);
        if ($this->shipping != null && $this->shipping != '0000-00-00') {
            $shipping = strtotime($this->shipping);
            $string .= _i(', in consegna %s', [printableDate($shipping)]);
        }

        return $string;
    }

    public function printableHeader()
    {
        return $this->printableName() . $this->headerIcons() . sprintf('<br/><small>%s</small>', $this->printableDates());
    }

    public function getBookingURL()
    {
        return route('booking.index') . '#' . $this->aggregate->id;
    }

    public function userBooking($userid = null)
    {
        $userobj = null;

        if (is_null($userid)) {
            $userobj = Auth::user();
            $userid = $userobj->id;
        }
        elseif (is_object($userid)) {
            $userobj = $userid;
            $userid = $userobj->id;
        }

        $ret = $this->bookings->where('user_id', $userid)->first();

        if (is_null($ret)) {
            $ret = new Booking();
            $ret->user_id = $userid;
            $ret->order_id = $this->id;
            $ret->notes = '';
            $ret->status = 'pending';
            $ret->id = $ret->getSlugID();
        }
        else {
            $ret->loadMissing(['products', 'products.modifiedValues']);
            $ret->products->each(fn ($p) => $p->setRelation('booking', $ret));
        }

        if ($userobj) {
            $ret->setRelation('user', $userobj);
        }

        $ret->setRelation('order', $this);

        return $ret;
    }

    public function topLevelBookings($status = null)
    {
        $ret = [];

        if (is_null($status)) {
            $bookings = $this->bookings()->get();
        }
        else {
            $bookings = $this->bookings()->where('status', $status)->get();
        }

        foreach ($bookings as $booking) {
            $booking->setRelation('order', $this);

            if ($booking->user->isFriend()) {
                if (! isset($ret[$booking->user->parent_id])) {
                    $ret[$booking->user->parent_id] = $this->userBooking($booking->user->parent_id);
                }
            }
            else {
                $ret[$booking->user->id] = $booking;
            }
        }

        return $ret;
    }

    public function getInternalNumberAttribute()
    {
        return app()->make('OrderNumbersDispatcher')->getNumber($this);
    }

    public function getLongCommentAttribute()
    {
        if (! empty($this->comment) && strlen($this->comment) >= longCommentLimit()) {
            return $this->comment;
        }
        else {
            return '';
        }
    }

    /*
        Se il prodotto è contenuto nell'ordine la funzione ritorna TRUE
        e la referenza a $product viene sostituita con quella interna
        all'ordine stesso, per poter accedere ai valori nella tabella
        pivot
    */
    public function hasProduct(&$product): bool
    {
        /*
            Non usare qui una query diretta
            $this->products()->where(...)
            in modo da sfruttare la copia di products cachata in $this
        */
        foreach ($this->products as $p) {
            if ($p->id == $product->id) {
                $product = $p;

                return true;
            }
        }

        return false;
    }

    /*
        Questa funzione è per estrapolare i prezzi dei prodotti e fissarli
        all'interno dell'ordine. Pertanto occorre qui accedere direttamente ai
        prezzi di tali prodotti e varianti, non dalla funzione getPrice()
    */
    private function extractProductPrices($product)
    {
        $row = [
            'unit_price' => $product->price,
        ];

        $variants = $product->variant_combos;
        if ($variants->isEmpty() === false) {
            $row['variants'] = [];

            foreach ($variants as $variant) {
                $id = $variant->innerIdentifier();
                $row['variants'][$id] = $variant->price_offset;
            }
        }

        return json_encode($row);
    }

    /*
        Dato il nuovo elenco di prodotti abilitati nell'ordine, verifica la
        consistenza delle relative prenotazioni.
        Se ci sono prodotti già prenotati ma che non appaiono nel suddetto
        elenco, l'intera applicazione va in errore
    */
    private function checkConsistency($new_products)
    {
        $order_id = $this->id;

        $booked_products = DB::table('booked_products')->select('product_id')->distinct()->join('bookings', function ($join) use ($order_id) {
            $join->on('booking_id', '=', 'bookings.id')->where('order_id', $order_id);
        })->get();

        $products_ids = $new_products->pluck('id')->toArray();

        foreach ($booked_products as $bp) {
            if (in_array($bp->product_id, $products_ids) === false) {
                throw new \DomainException("Un prodotto già prenotato non è nell'elenco dei nuovi prodotti per l'ordine! Ordine: " . $this->id . ', prodotto: ' . $bp->product_id, 1);
            }
        }
    }

    public function syncProducts($products, $update_prices)
    {
        $this->checkConsistency($products);

        if ($update_prices) {
            $data = [];

            foreach ($products as $product) {
                $data[$product->id] = [
                    'prices' => $this->extractProductPrices($product),
                ];
            }

            $this->products()->sync($data);
        }
        else {
            $this->products()->sync($products->pluck('id')->toArray());
        }
    }

    public function attachProduct($product)
    {
        $prices = $this->extractProductPrices($product);

        $exists = $this->products->firstWhere('id', $product->id);
        if ($exists) {
            $this->products()->updateExistingPivot($product->id, [
                'prices' => $prices,
            ]);
        }
        else {
            $this->products()->attach($product->id, [
                'prices' => $prices,
            ]);
        }
    }

    public function detachProduct($product)
    {
        $altered_bookings = 0;

        /*
            Se vengono rimossi dei prodotti dall'ordine, ne elimino tutte le
            relative prenotazioni sinora avvenute
        */
        foreach ($this->bookings as $booking) {
            $products = $booking->products()->where('product_id', $product->id)->get();
            if ($products->isEmpty() === false) {
                $altered_bookings++;
                foreach ($products as $p) {
                    $p->delete();
                }
            }

            /*
                Se i prodotti rimossi erano gli unici contemplati nella
                prenotazione, elimino tutta la prenotazione
            */
            if ($booking->products()->count() == 0) {
                $booking->delete();
            }
        }

        $this->products()->detach($product->id);
        \Log::info('Rimosso prodotto ' . $product->id . ' da ordine ' . $this->id . ', alterate ' . $altered_bookings . ' prenotazioni');
    }

    public function showableContacts()
    {
        $gas = currentAbsoluteGas();
        $ret = null;

        switch ($gas->booking_contacts) {
            case 'none':
                $ret = new Collection();
                break;

            case 'manual':
                $ret = $this->users;
                break;

            default:
                $role = Role::find($gas->booking_contacts);
                if ($role) {
                    $ret = $role->usersByTarget($this->supplier);
                }
                else {
                    $ret = new Collection();
                }
        }

        return $ret;
    }

    public function enforcedContacts()
    {
        return $this->innerCache('enforced_contacts', function ($obj) {
            $contacts = $obj->showableContacts();
            if ($contacts->isEmpty()) {
                $contacts = everybodyCan('supplier.orders', $obj->supplier);
            }

            return $contacts;
        });
    }

    public function notifiableUsers($gas)
    {
        $order = $this;

        if ($gas->notify_all_new_orders) {
            $query_users = User::whereNull('parent_id');
        }
        else {
            $query_users = User::whereHas('suppliers', function ($query) use ($order) {
                $query->where('suppliers.id', $order->supplier->id);
            });
        }

        $query_users->fullEnabled();

        $user_circles = $order->circles()->whereHas('group', function ($query) {
            $query->where('context', 'user')->where('filters_orders', true);
        })->pluck('id');

        if ($user_circles->isEmpty() == false) {
            $query_users->whereHas('circles', function ($query) use ($user_circles) {
                $query->whereIn('id', $user_circles);
            });
        }

        $query_users->whereHas('contacts', function ($query) {
            $query->where('type', 'email');
        });

        return $query_users->get();
    }

    public function angryBookings()
    {
        $had_cache = $this->hasInnerCache('angry_bookings');

        $bookings = $this->innerCache('angry_bookings', function ($obj) {
            $bookings = $obj->bookings()->angryload()->get();

            foreach ($bookings as $booking) {
                $booking->setRelation('order', $obj);
            }

            return $bookings;
        });

        /*
            Questa funzione viene usata in particolare in applyModifiers(),
            talvolta in due invocazioni consecutive per ottenere i modificatori
            del prenotato e del consegnato. Ma la prima invocazione popola i
            modificatori all'interno dell'albero delle prenotazioni dell'ordine,
            e la seconda - trovandoli già a posto - li riusa impropriamente. Il
            risultato è che i modificatori del consegnato risultano gli stessi
            del prenotato.
            Anziché rinunciare completamente alla cache aggressiva sulle
            prenotazioni, preferisco invalidare e ricalcolare solo i
            modificatori quando necessario
        */
        if ($had_cache) {
            foreach ($bookings as $booking) {
                $booking->unsetModifiedValues();
            }
        }

        $this->setRelation('bookings', $bookings);

        return $bookings;
    }

    public function isActive(): bool
    {
        return $this->status != 'shipped' && $this->status != 'archived';
    }

    public function isRunning(): bool
    {
        return ($this->status == 'open') || ($this->status == 'closed' && $this->keep_open_packages != 'no' && $this->pendingPackages()->isEmpty() === false);
    }

    public function pendingPackages(): Collection
    {
        return $this->innerCache('pending_packages', function ($obj) {
            $ret = new Collection();
            $products = $obj->products()->where('package_size', '!=', 0)->with('measure')->get();

            if ($products->isEmpty() === false) {
                $order = $this;
                $order_data = app()->make('GlobalScopeHub')->executedForAll($this->keep_open_packages != 'each', function () use ($order) {
                    return $order->reduxData();
                });

                foreach ($products as $p) {
                    $quantity = $order_data->products[$p->id]->quantity ?? 0;
                    if ($quantity != 0) {
                        $test = round(fmod($quantity, $p->fixed_package_size));
                        if ($test != 0) {
                            $fake_max_available = 0;
                            while ($fake_max_available < $quantity) {
                                $fake_max_available += $p->fixed_package_size;
                            }

                            $p->is_pending_package = true;
                            $p->max_available = $fake_max_available;
                            $ret->push($p);
                        }
                    }
                }
            }

            return $ret;
        });
    }

    public function calculateInvoicingSummary()
    {
        $summary = (object) [
            'order' => $this->id,
            'total' => 0,
            'total_taxable' => 0,
            'total_tax' => 0,
            'products' => [],
        ];

        $order = $this;
        $products = $order->products;

        $global_total = 0;
        $global_total_taxable = 0;
        $global_total_tax = 0;
        $rates = [];

        foreach ($products as $product) {
            $query = BookedProduct::with('variants')->with('booking')->where('product_id', '=', $product->id)->whereHas('booking', function ($query) use ($order) {
                $query->where('order_id', '=', $order->id);
            });

            $price_delivered = $query->sum('final_price');
            $quantity_delivered = $query->sum('delivered');

            if (isset($rates[$product->vat_rate_id]) === false) {
                $rates[$product->vat_rate_id] = $product->vat_rate;
            }

            $rate = $rates[$product->vat_rate_id];
            if ($rate != null) {
                $total = $price_delivered / (1 + ($rate->percentage / 100));
                $total_vat = $price_delivered - $total;
            }
            else {
                $total = $price_delivered;
                $total_vat = 0;
            }

            $summary->products[$product->id]['total'] = printablePrice($total);
            $summary->products[$product->id]['total_vat'] = printablePrice($total_vat);
            $summary->products[$product->id]['delivered'] = printableQuantity($quantity_delivered, $product->measure->discrete);

            $global_total += $price_delivered;
            $global_total_taxable += $total;
            $global_total_tax += $total_vat;
        }

        $summary->total = printablePrice($global_total);
        $summary->total_taxable = printablePrice($global_total_taxable);
        $summary->total_tax = printablePrice($global_total_tax);

        return $summary;
    }

    public static function displayColumns()
    {
        $ret = [
            'selection' => (object) [
                'label' => _i('Selezione'),
                'help' => _i("Per abilitare o disabilitare prodotti del listino fornitore all'interno dell'ordine"),
                'width' => 3,
            ],
            'name' => (object) [
                'label' => _i('Prodotto'),
                'help' => _i('Nome e descrizione del prodotto'),
                'width' => 20,
            ],
            'price' => (object) [
                'label' => _i('Prezzo'),
                'help' => _i('Prezzo unitario del prodotto'),
                'width' => 5,
            ],
            'available' => (object) [
                'label' => _i('Disponibile'),
                'help' => _i('Quantità disponibile del prodotto'),
                'width' => 5,
            ],
        ];

        /*
            I modificatori dei prodotti vengono resi accessibili direttamente
            nella tabella dell'ordine, per poter essere consultati prodotto per
            prodotto.
            In summary.blade.php si provvede poi a nascondere del tutto le
            colonne per i modificatori che non sono stati attivati per nessun
            prodotto all'interno dell'ordine
        */
        $products_modifiers = ModifierType::byClass(Product::class);
        foreach ($products_modifiers as $pmod) {
            $ret['modifier-pending-' . $pmod->id] = (object) [
                'label' => sprintf('%s (%s)', $pmod->name, _i('Prenotato')),
                'help' => _i("Modificatore Prodotto, sul Prenotato. Mostrato solo se il modificatore è attivo per un qualche prodotto nell'ordine"),
                'width' => 7,
            ];

            $ret['modifier-shipped-' . $pmod->id] = (object) [
                'label' => sprintf('%s (%s)', $pmod->name, _i('Consegnato')),
                'help' => _i("Modificatore Prodotto, sul Consegnato. Mostrato solo se il modificatore è attivo per un qualche prodotto nell'ordine"),
                'width' => 7,
            ];
        }

        $ret = $ret + [
            'unit_measure' => (object) [
                'label' => _i('Unità di Misura'),
                'help' => _i('Unità di misura assegnata al prodotto'),
                'width' => 9,
            ],
            'quantity' => (object) [
                'label' => _i('Quantità Prenotata'),
                'help' => _i('Quantità complessivamente prenotata del prodotto'),
                'width' => 8,
            ],
            'weight' => (object) [
                'label' => _i('Peso Prenotato'),
                'help' => _i('Peso complessivamente prenotato del prodotto'),
                'width' => 8,
            ],
            'total_price' => (object) [
                'label' => _i('Totale Prezzo'),
                'help' => _i('Totale prezzo della quantità prenotata'),
                'width' => 8,
            ],
            'quantity_delivered' => (object) [
                'label' => _i('Quantità Consegnata'),
                'help' => _i('Quantità complessivamente consegnata del prodotto'),
                'width' => 8,
            ],
            'weight_delivered' => (object) [
                'label' => _i('Peso Consegnato'),
                'help' => _i('Peso complessivamente consegnato del prodotto'),
                'width' => 8,
            ],
            'price_delivered' => (object) [
                'label' => _i('Totale Consegnato'),
                'help' => _i('Totale prezzo della quantità consegnata'),
                'width' => 8,
            ],
            'notes' => (object) [
                'label' => _i('Note'),
                'help' => _i('Pannello da cui modificare direttamente le quantità di prodotto in ogni prenotazione, ed aggiungere note per il fornitore'),
                'width' => 3,
            ],
        ];

        return $ret;
    }

    public function getPermissionsProxies()
    {
        return [$this->supplier];
    }

    public function involvedModifiers($include_groups = false)
    {
        $key = 'involved_modifiers_' . ($include_groups ? 'groups' : 'no_groups');

        return $this->innerCache($key, function ($obj) use ($include_groups) {
            $modifiers = $this->modifiers;

            foreach ($obj->products as $product) {
                $modifiers = $modifiers->merge($product->modifiers);
            }

            if ($include_groups) {
                $managed_circles = [];

                foreach ($this->bookings as $booking) {
                    foreach ($booking->involvedCircles() as $circle) {
                        if (isset($managed_circles[$circle->id]) == false) {
                            $managed_circles[$circle->id] = true;
                            $modifiers = $modifiers->merge($circle->modifiers);
                        }
                    }
                }
            }

            return $modifiers->filter(function ($mod) {
                return $mod->active;
            })->sortBy('priority');
        });
    }

    public function applyModifiers($aggregate_data = null, $enforce_status = false): Collection
    {
        $modifiers = new Collection();
        $order_modifiers = $this->involvedModifiers(true);

        /*
            TODO: se ci sono prenotazioni consegnate, non posso fidarmi
            dell'elenco teorico dei modificatori in quanto potrebbero essercene
            altri assegnati (e.g. quelli usati per le consegne manuali) dunque
            devo andarli a leggere direttamente dalle prenotazioni stesse.
            D'altro canto, per le prenotazioni consegnate non serve ottenere una
            Redux dell'ordine in quanto appunto vado a leggermi i dati dai
            modificatori già calcolati e salvati sul DB, dunque compio questa
            (lenta) operazione inutilmente.
            Questa funzione potrebbe essere un po' più complessa, e generare una
            Redux complessiva solo in presenza di prenotazioni non consegnate
        */
        $has_shipped_bookings = $this->bookings->where('status', '!=', 'pending')->count() != 0;

        if ($order_modifiers->isEmpty() === false || $has_shipped_bookings) {
            DB::beginTransaction();

            if (is_null($aggregate_data)) {
                $aggregate_data = $this->minimumRedux($order_modifiers);
            }

            $old_status = $this->status;
            if ($enforce_status !== false) {
                $this->status = $enforce_status;
            }

            $bookings = $this->angryBookings();

            foreach ($bookings as $booking) {
                $booking->setRelation('order', $this);

                if ($enforce_status !== false) {
                    $booking->status = $enforce_status;
                }

                /*
                    Reminder: qui vengono iterate tutte le prenotazioni
                    nell'ordine, incluse quelle degli amici, dunque i
                    modificatori vanno applicati su ciascuna in modo
                    indipendente
                */
                $modifiers = $modifiers->merge($booking->applyModifiers($aggregate_data));
            }

            $this->status = $old_status;
            DB::rollback();
        }

        return $modifiers;
    }

    /*
        Questo restituisce l'ammontare totale dovuto al fornitore, includendo
        sia il valore delle prenotazioni consegnate che quello degli eventuali
        modificatori che coinvolgono il fornitore stesso.
        Nota bene: un diverso approccio sarebbe quello di fare la somma diretta
        del totale dei movimenti che fanno riferimento alle prenotazioni
        dell'ordine, ma ci sarebbero comunque da considerare i movimenti
        innescati dai modificatori con uno specifico tipo (che a volte impattano
        sul saldo del fornitore, ma a volte no)
    */
    public function fullSupplierValue($aggregate_data, $modifiers)
    {
        $total = $aggregate_data->price_delivered;

        foreach ($modifiers as $value) {
            if ($value->activeMath()) {
                $movement_type = $value->modifier->movementType;
                if (is_null($movement_type)) {
                    $total += $value->effective_amount;
                }
                else {
                    if ($movement_type->target_type == Supplier::class || $movement_type->sender_type == Supplier::class) {
                        $total += $value->effective_amount;
                    }
                }
            }
        }

        return $total;
    }

    /*
        Se l'ordine non è più attivo, confronta i valori dei modificatori
        trasversalmente applicati tra le prenotazioni e restituisce quelli il
        cui valore assoluto definito non corrisponde al valore effettivamente
        distribuito (probabilmente perché le quantità consegnate non
        corrispondono a quelle prenotate).
        Se ci sono delle discrepanze, possono essere risolte da
        OrdersController::postFixModifiers()
    */
    public function unalignedModifiers($master_summary)
    {
        if ($this->isActive()) {
            return [];
        }

        $ret = [];

        $pending_modifiers = ModifiedValue::aggregateByType($this->applyModifiers($master_summary, 'pending'));
        $shipped_modifiers = ModifiedValue::aggregateByType($this->applyModifiers($master_summary, 'shipped'));

        foreach ($pending_modifiers as $pending_id => $pending_mod) {
            foreach ($shipped_modifiers as $shipped_id => $shipped_mod) {
                if ($pending_id == $shipped_id && round($pending_mod->amount, 2) != round($shipped_mod->amount, 2)) {
                    $ret[] = (object) [
                        'pending' => $pending_mod,
                        'shipped' => $shipped_mod,
                    ];
                }
            }
        }

        return $ret;
    }

    /********************************************************* ReducibleTrait */

    protected function reduxBehaviour()
    {
        $ret = $this->emptyReduxBehaviour();

        $ret->children = function ($item, $filters) {
            $bookings = $filters['bookings'] ?? null;

            if (is_null($bookings)) {
                $bookings = $item->angryBookings();

                $circles = $filters['circles'] ?? null;
                if ($circles) {
                    $bookings = $circles->sortBookings($bookings);
                }
            }

            return $bookings;
        };

        $ret->collected = 'bookings';
        $ret->merged = 'products';

        return $ret;
    }

    /************************************************************ SluggableID */

    public function getSlugID()
    {
        $start = Carbon::parse($this->start)->isoFormat('DD MMMM YYYY');

        return sprintf('%s::%s', $this->supplier->id, Str::slug($start));
    }

    /******************************************************** CreditableTrait */

    public function getBalanceProxy()
    {
        return $this->supplier;
    }

    public function balanceFields()
    {
        return [
            'bank' => _i('Saldo Fornitore'),
        ];
    }

    /******************************************************** ExportableTrait */

    public function exportXML()
    {
        return view('gdxp.xml.supplier', ['obj' => $this->supplier, 'orders' => [$this]])->render();
    }

    public function exportJSON()
    {
        $hub = app()->make('GlobalScopeHub');
        $gas = Gas::find($hub->getGas());

        return view('gdxp.json.supplier', ['obj' => $this->supplier, 'order' => $this, 'currentgas' => $gas])->render();
    }

    /******************************************************** ModifiableTrait */

    public function inheritModificationTypes()
    {
        return $this->supplier;
    }

    /************************************************************** InCircles */

    public function eligibleGroups()
    {
        return Group::where('context', 'booking')->orWhere(function ($query) {
            $query->where('context', 'user')->where('filters_orders', true);
        })->orderBy('name', 'asc')->get();
    }
}
