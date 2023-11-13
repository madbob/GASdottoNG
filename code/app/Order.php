<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

use App;
use Auth;
use DB;
use Mail;
use URL;
use Log;
use Carbon\Carbon;

use App\Models\Concerns\AttachableTrait;
use App\Models\Concerns\PayableTrait;
use App\Models\Concerns\CreditableTrait;
use App\Models\Concerns\ModifiableTrait;
use App\Models\Concerns\ExportableTrait;
use App\Models\Concerns\ReducibleTrait;
use App\Scopes\RestrictedGAS;
use App\Events\SluggableCreating;

class Order extends Model
{
    use HasFactory, AttachableTrait, ExportableTrait, ModifiableTrait, PayableTrait, CreditableTrait, GASModel, SluggableID, ReducibleTrait;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $dispatchesEvents = [
        'creating' => SluggableCreating::class
    ];

    protected static function boot()
    {
        parent::boot();
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

    public function deliveries(): BelongsToMany
    {
        return $this->belongsToMany('App\Delivery');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany('App\User');
    }

    public function printableName()
    {
        $ret = $this->supplier->name;

        if (!empty($this->comment) && strlen($this->comment) < longCommentLimit()) {
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

    public function scopeAccessibleBooking($query)
    {
        $user = Auth::user();

        if ($user && $user->gas->hasFeature('shipping_places')) {
            $query->where(function($query) use ($user) {
                $query->where(function($query) use ($user) {
                    $query->doesnthave('deliveries')->orWhereHas('deliveries', function($query) use ($user) {
                        if ($user->isFriend()) {
                            $shippingplace = $user->parent->shippingplace;
                        }
                        else {
                            $shippingplace = $user->shippingplace;
                        }

                        if (is_null($shippingplace)) {
                            $query->where('delivery_id', '!=', '0');
                        }
                        else {
                            $query->where('delivery_id', $shippingplace->id);
                        }
                    });
                });
            });
        }
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
        else if (is_object($userid)) {
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
            $ret->products->each(fn($p) => $p->setRelation('booking', $ret));
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

        foreach($bookings as $booking) {
            $booking->setRelation('order', $this);

            if ($booking->user->isFriend()) {
                if (!isset($ret[$booking->user->parent_id])) {
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
        return App::make('OrderNumbersDispatcher')->getNumber($this);
    }

    public function getLongCommentAttribute()
    {
        if (!empty($this->comment) && strlen($this->comment) >= longCommentLimit()) {
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
    public function hasProduct(&$product)
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
        if ($variants->isEmpty() == false) {
            $row['variants'] = [];

            foreach($variants as $variant) {
                $id = $variant->innerIdentifier();
                $row['variants'][$id] = $variant->price_offset;
            }
        }

        return json_encode($row);
    }

    public function syncProducts($products)
    {
        $data = [];

        foreach($products as $product) {
            $data[$product->id] = [
                'prices' => $this->extractProductPrices($product),
            ];
        }

        $this->products()->sync($data);
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

    public function showableContacts()
    {
        $gas = currentAbsoluteGas();

        switch($gas->booking_contacts) {
            case 'none':
                return new Collection();

            case 'manual':
                return $this->users;

            default:
                $role = Role::find($gas->booking_contacts);
                if ($role) {
                    return $role->usersByTarget($this->supplier);
                }

                return new Collection();
        }
    }

    public function enforcedContacts()
    {
        return $this->innerCache('enforced_contacts', function($obj) {
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

        if ($gas->getConfig('notify_all_new_orders')) {
            $query_users = User::whereNull('parent_id');
        }
        else {
            $query_users = User::whereHas('suppliers', function($query) use ($order) {
                $query->where('suppliers.id', $order->supplier->id);
            });
        }

        $deliveries = $order->deliveries;
        if ($deliveries->isEmpty() == false) {
            $query_users->where(function($query) use ($deliveries) {
                $query->whereIn('preferred_delivery_id', $deliveries->pluck('id'))->orWhere('preferred_delivery_id', '0');
            });
        }

        $query_users->whereHas('contacts', function($query) {
            $query->where('type', 'email');
        });

        return $query_users->get();
    }

    public function angryBookings()
    {
        $bookings = $this->innerCache('angry_bookings', function($obj) {
            $bookings = $obj->bookings()->angryload()->get();

            foreach($bookings as $booking) {
                $booking->setRelation('order', $obj);
            }

            return $bookings;
        });

        $this->setRelation('bookings', $bookings);
        return $bookings;
    }

    public function isActive()
    {
        return $this->status != 'shipped' && $this->status != 'archived';
    }

    public function isRunning()
    {
        return (($this->status == 'open') || ($this->status == 'closed' && $this->keep_open_packages != 'no' && $this->pendingPackages()->isEmpty() == false));
    }

    public function pendingPackages()
    {
        return $this->innerCache('pending_packages', function($obj) {
            $ret = new Collection();
            $products = $obj->products()->where('package_size', '!=', 0)->with('measure')->get();

            if ($products->isEmpty() == false) {
                $order = $this;
                $order_data = App::make('GlobalScopeHub')->executedForAll($this->keep_open_packages != 'each', function() use ($order) {
                    return $order->reduxData();
                });

                foreach($products as $p) {
                    $quantity = $order_data->products[$p->id]->quantity ?? 0;
                    if ($quantity != 0) {
                        $test = round(fmod($quantity, $p->fixed_package_size));
                        if ($test != 0) {
                            $fake_max_available = 0;
                            while($fake_max_available < $quantity) {
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

            if (isset($rates[$product->vat_rate_id]) == false) {
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
                'width' => 3
            ],
            'name' => (object) [
                'label' => _i('Prodotto'),
                'help' => _i('Nome e descrizione del prodotto'),
                'width' => 20
            ],
            'price' => (object) [
                'label' => _i('Prezzo'),
                'help' => _i('Prezzo unitario del prodotto'),
                'width' => 5
            ],
            'available' => (object) [
                'label' => _i('Disponibile'),
                'help' => _i('Quantità disponibile del prodotto'),
                'width' => 5
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
        foreach($products_modifiers as $pmod) {
            $ret['modifier-pending-' . $pmod->id] = (object) [
                'label' => sprintf('%s (%s)', $pmod->name, _i('Prenotato')),
                'help' => _i("Modificatore Prodotto, sul Prenotato. Mostrato solo se il modificatore è attivo per un qualche prodotto nell'ordine"),
                'width' => 7
            ];

            $ret['modifier-shipped-' . $pmod->id] = (object) [
                'label' => sprintf('%s (%s)', $pmod->name, _i('Consegnato')),
                'help' => _i("Modificatore Prodotto, sul Consegnato. Mostrato solo se il modificatore è attivo per un qualche prodotto nell'ordine"),
                'width' => 7
            ];
        }

        $ret = $ret + [
            'unit_measure' => (object) [
                'label' => _i('Unità di Misura'),
                'help' => _i('Unità di misura assegnata al prodotto'),
                'width' => 9
            ],
            'quantity' => (object) [
                'label' => _i('Quantità Prenotata'),
                'help' => _i('Quantità complessivamente prenotata del prodotto'),
                'width' => 8
            ],
            'weight' => (object) [
                'label' => _i('Peso Prenotato'),
                'help' => _i('Peso complessivamente prenotato del prodotto'),
                'width' => 8
            ],
            'total_price' => (object) [
                'label' => _i('Totale Prezzo'),
                'help' => _i('Totale prezzo della quantità prenotata'),
                'width' => 8
            ],
            'quantity_delivered' => (object) [
                'label' => _i('Quantità Consegnata'),
                'help' => _i('Quantità complessivamente consegnata del prodotto'),
                'width' => 8
            ],
            'weight_delivered' => (object) [
                'label' => _i('Peso Consegnato'),
                'help' => _i('Peso complessivamente consegnato del prodotto'),
                'width' => 8
            ],
            'price_delivered' => (object) [
                'label' => _i('Totale Consegnato'),
                'help' => _i('Totale prezzo della quantità consegnata'),
                'width' => 8
            ],
            'notes' => (object) [
                'label' => _i('Note'),
                'help' => _i('Pannello da cui modificare direttamente le quantità di prodotto in ogni prenotazione, ed aggiungere note per il fornitore'),
                'width' => 3
            ],
        ];

        return $ret;
    }

    public function getPermissionsProxies()
    {
        return [$this->supplier];
    }

    public function involvedModifiers($include_shipping_places = false)
    {
        $key = 'involved_modifiers_' . ($include_shipping_places ? 'shipping' : 'no_shipping');

        return $this->innerCache($key, function($obj) use ($include_shipping_places) {
            $modifiers = $this->modifiers;

            foreach ($this->products as $product) {
                $modifiers = $modifiers->merge($product->modifiers);
            }

            if ($include_shipping_places) {
                $managed_shipping_places = [];

                foreach ($this->bookings as $booking) {
                    $booker = $booking->user;
                    if ($booker->shippingplace && !isset($managed_shipping_places[$booker->shippingplace->id])) {
                        $managed_shipping_places[$booker->shippingplace->id] = true;
                        $modifiers = $modifiers->merge($booker->shippingplace->modifiers);
                    }
                }
            }

            return $modifiers->filter(function($mod) {
                return $mod->active;
            })->sortBy('priority');
        });
    }

    public function applyModifiers($aggregate_data = null, $enforce_status = false)
    {
        $modifiers = $this->involvedModifiers(true);

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

        if ($modifiers->isEmpty() == false || $has_shipped_bookings) {
            DB::beginTransaction();

            if (is_null($aggregate_data)) {
                $aggregate_data = $this->minimumRedux($modifiers);
            }

            $modifiers = new Collection();

            $old_status = $this->status;
            if ($enforce_status !== false) {
                $this->status = $enforce_status;
            }

            $bookings = $this->angryBookings();

            foreach($bookings as $booking) {
                $booking->setRelation('order', $this);

                if ($enforce_status !== false) {
                    $booking->status = $enforce_status;
                }

                $modifiers = $modifiers->merge($booking->applyModifiers($aggregate_data));
            }

            $this->status = $old_status;
            DB::rollback();
        }

        return $modifiers;
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

        foreach($pending_modifiers as $pending_id => $pending_mod) {
            foreach($shipped_modifiers as $shipped_id => $shipped_mod) {
                if ($pending_id == $shipped_id) {
                    if (round($pending_mod->amount, 2) != round($shipped_mod->amount, 2)) {
                        $ret[] = (object) [
                            'pending' => $pending_mod,
                            'shipped' => $shipped_mod,
                        ];
                    }
                }
            }
        }

        return $ret;
    }

    /********************************************************* ReducibleTrait */

    protected function reduxBehaviour()
    {
        $ret = $this->emptyReduxBehaviour();

        $ret->children = function($item, $filters) {
            $bookings = $filters['bookings'] ?? null;

            if (is_null($bookings)) {
                $bookings = $item->angryBookings();

                $shipping_place = $filters['shipping_place'] ?? null;
                if ($shipping_place) {
                    $bookings = $bookings->filter(function($booking) use ($shipping_place) {
                        $sp = $booking->shipping_place;
                        return $sp && $sp->id == $shipping_place;
                    });
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
        $hub = App::make('GlobalScopeHub');
        $gas = Gas::find($hub->getGas());
        return view('gdxp.json.supplier', ['obj' => $this->supplier, 'order' => $this, 'currentgas' => $gas])->render();
    }

    /******************************************************** ModifiableTrait */

    public function inheritModificationTypes()
    {
        return $this->supplier;
    }
}
