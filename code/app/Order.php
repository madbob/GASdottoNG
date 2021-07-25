<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

use App;
use Auth;
use DB;
use PDF;
use Mail;
use URL;
use Log;

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

    public function supplier()
    {
        /*
            La rimozione dello scope globale serve nel caso del Multi-GAS, per
            accedere al fornitore di un ordine anche se il fornitore stesso non
            è visibile al GAS
        */
        return $this->belongsTo('App\Supplier')->withoutGlobalScopes()->withTrashed();
    }

    public function aggregate()
    {
        return $this->belongsTo('App\Aggregate');
    }

    public function products()
    {
        return $this->belongsToMany('App\Product')->with(['variants', 'modifiers'])->withPivot('notes')->withTrashed()->orderBy('name');
    }

    public function bookings()
    {
        return $this->hasMany('App\Booking')->with(['user', 'products'])->sorted();
    }

    public function payment()
    {
        return $this->belongsTo('App\Movement');
    }

    public function invoice()
    {
        return $this->belongsToMany('App\Invoice');
    }

    public function deliveries()
    {
        return $this->belongsToMany('App\Delivery');
    }

    public function users()
    {
        return $this->belongsToMany('App\User');
    }

    public function printableName()
    {
        $ret = $this->supplier->name;

        if (!empty($this->comment) && strlen($this->comment) < self::longCommentLimit()) {
            $ret .= ' - ' . $this->comment;
        }

        $ret .= ' - ' . $this->internal_number;

        return $ret;
    }

    public function scopeAccessibleBooking($query)
    {
        $user = Auth::user();

        if ($user->gas->hasFeature('shipping_places')) {
            $query->where(function($query) use ($user) {
                $query->where(function($query) use ($user) {
                    $query->doesnthave('deliveries')->orWhereHas('deliveries', function($query) use ($user) {
                        $query->where('delivery_id', $user->preferred_delivery_id);
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
            $string .= _i(', in consegna %s', printableDate($shipping));
        }

        return $string;
    }

    public function printableHeader()
    {
        return $this->printableName() . $this->headerIcons() . sprintf('<br/><small>%s</small>', $this->printableDates());
    }

    public function getBookingURL()
    {
        return URL::action('BookingController@index').'#' . $this->aggregate->id;
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

        $ret = $this->hasMany('App\Booking')->whereHas('user', function ($query) use ($userid) {
            $query->where('id', '=', $userid);
        })->first();

        if (is_null($ret)) {
            $ret = new Booking();
            $ret->user_id = $userid;
            $ret->order_id = $this->id;
            $ret->notes = '';
            $ret->status = 'pending';
            $ret->notes = '';
            $ret->id = $ret->getSlugID();
        }

        if ($ret && $userobj) {
            $ret->setRelation('user', $userobj);
        }

        return $ret;
    }

    public function topLevelBookings($status = null)
    {
        $ret = [];

        if (is_null($status))
            $bookings = $this->bookings()->get();
        else
            $bookings = $this->bookings()->where('status', $status)->get();

        foreach($bookings as $booking) {
            if ($booking->user->isFriend()) {
                if (!isset($ret[$booking->user->parent_id])) {
                    $placeholder = new Booking();
                    $placeholder->user_id = $booking->user->parent_id;
                    $placeholder->order_id = $this->id;
                    $ret[$booking->user->parent_id] = $placeholder;
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
        if (!empty($this->comment) && strlen($this->comment) >= self::longCommentLimit()) {
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

    public function showableContacts()
    {
        $gas = currentAbsoluteGas();

        switch($gas->booking_contacts) {
            case 'none':
                return new Collection();
                break;

            case 'manual':
                return $this->users;
                break;

            default:
                $role = Role::find($gas->booking_contacts);
                if ($role) {
                    return $role->usersByTarget($this->supplier);
                }
                else {
                    Log::error('Role not found while displaying contacts for order: ' . $gas->booking_contacts);
                    return new Collection();
                }

                break;
        }
    }

    public function enforcedContacts()
    {
        return $this->innerCache('enforced_contacts', function($obj) {
            $contacts = $obj->showableContacts();
            if ($contacts->isEmpty()) {
                $contacts = Role::everybodyCan('supplier.orders', $obj->supplier);
            }

            return $contacts;
        });
    }

    public static function longCommentLimit()
    {
        return 100;
    }

    private function autoGuessFields()
    {
        $has_code = false;
        $has_boxes = false;

        foreach($this->products as $product) {
            if (!empty($product->code)) {
                $has_code = true;
            }

            if ($product->package_size != 0) {
                $has_boxes = true;
            }

            if ($has_code && $has_boxes) {
                break;
            }
        }

        $guessed_fields = [];

        if ($has_code) {
            $guessed_fields[] = 'code';
        }

        $guessed_fields[] = 'name';
        $guessed_fields[] = 'quantity';

        if ($has_boxes) {
            $guessed_fields[] = 'boxes';
        }

        $guessed_fields[] = 'measure';
        $guessed_fields[] = 'unit_price';
        $guessed_fields[] = 'price';

        return $guessed_fields;
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

    public function document($type, $format, $action, $required_fields, $status, $shipping_place)
    {
        if (empty($required_fields)) {
            $required_fields = $this->autoGuessFields();
        }

        switch($type) {
            case 'summary':
                $data = $this->formatSummary($required_fields, $status, $shipping_place);
                $title = _i('Prodotti ordine %s presso %s', [$this->internal_number, $this->supplier->name]);
                $filename = sanitizeFilename($title . '.' . $format);
                $temp_file_path = sprintf('%s/%s', sys_get_temp_dir(), $filename);

                if ($format == 'pdf') {
                    $pdf = PDF::loadView('documents.order_summary_pdf', ['order' => $this, 'blocks' => [$data]]);

                    if ($action == 'save') {
                        $pdf->save($temp_file_path);
                    }
                    else {
                        return $pdf->download($filename);
                    }
                }
                else if ($format == 'csv') {
                    if ($action == 'save') {
                        output_csv($filename, $data->headers, $data->contents, function($row) {
                            return $row;
                        }, $temp_file_path);
                    }
                    else {
                        return output_csv($filename, $data->headers, $data->contents, function($row) {
                            return $row;
                        });
                    }
                }
                else if ($format == 'gdxp') {
                    $contents = view('gdxp.json.supplier', ['obj' => $this->supplier, 'order' => $this, 'bookings' => true])->render();

                    if ($action == 'save') {
                        file_put_contents($temp_file_path, $contents);
                    }
                    else {
                        download_headers('application/json', $filename);
                        return $contents;
                    }
                }

                return $temp_file_path;
                break;
        }
    }

    public function calculateInvoicingSummary($products = null)
    {
        $summary = (object) [
            'order' => $this->id,
            'total' => 0,
            'total_taxable' => 0,
            'total_tax' => 0,
            'products' => [],
        ];

        $order = $this;

        if (is_null($products)) {
            $products = $order->products;
            $external_products = false;
        }
        else {
            $external_products = true;
        }

        $global_total = 0;
        $global_total_taxable = 0;
        $global_total_tax = 0;
        $rates = [];

        foreach ($products as $product) {
            $query = BookedProduct::with('variants')->with('booking')->where('product_id', '=', $product->id)->whereHas('booking', function ($query) use ($order) {
                $query->where('order_id', '=', $order->id);
            });

            $price_delivered = $query->sum('final_price') - $query->sum('final_discount');
            $quantity_delivered = $query->sum('delivered');

            if (isset($rates[$product->vat_rate_id]) == false)
                $rates[$product->vat_rate_id] = $product->vat_rate;

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

    private function formatProduct($fields, $formattable, $product_redux, $product, $internal_offsets)
    {
        if (is_null($product_redux)) {
            return [];
        }

        if (!empty($product_redux->variants)) {
            $variants_rows = [];
            $offset = $internal_offsets->by_variant;

            foreach ($product_redux->variants as $variant) {
                if ($variant->$offset == 0) {
                    continue;
                }

                $row = [];
                foreach($fields as $f) {
                    if (isset($formattable[$f])) {
                        if (isset($formattable[$f]->format_variant)) {
                            $row[] = call_user_func($formattable[$f]->format_variant, $product, $variant, $internal_offsets->alternate);
                        }
                        else {
                            $row[] = call_user_func($formattable[$f]->format_product, $product, $variant, $internal_offsets->alternate);
                        }
                    }
                }

                $variants_rows[] = $row;
            }

            usort($variants_rows, function($a, $b) {
                return $a[0] <=> $b[0];
            });

            return $variants_rows;
        }
        else {
            $offset = $internal_offsets->by_product;
            if ($product_redux->$offset == 0) {
                return [];
            }

            $row = [];
            foreach($fields as $f) {
                if (isset($formattable[$f])) {
                    $row[] = call_user_func($formattable[$f]->format_product, $product, $product_redux, $internal_offsets->alternate);
                }
            }

            return [$row];
        }
    }

    /*
        In mancanza di uno storico dei prezzi dei prodotti, qui si va ad
        alterare il database andando ad "indovinare" i prezzi all'epoca
        dell'ordine stesso inferendoli dalle prenotazioni.
        Da sopprimere prima o dopo con uno storico reale.
    */
    public function waybackProducts()
    {
        if ($this->isRunning()) {
            return;
        }

        /*
            Questa funzione va sempre sempre sempre eseguita all'interno di una
            transazione del DB, andando ad alterare i prezzi dei prodotti
        */
        if (DB::transactionLevel() <= 0) {
            throw new \Exception("Revert prezzi dell'ordine senza transazione attiva", 1);
        }

        $products = [];

        foreach ($this->bookings as $booking) {
            foreach($booking->products as $product) {
                $id = $product->product->id;

                if (!isset($products[$id])) {
                    $products[$id] = (object) [
                        'quantity' => 0,
                        'price' => 0,
                    ];
                }

                $products[$id]->quantity += $product->delivered;
                $products[$id]->price += $product->final_price;
            }
        }

        $altered = false;

        foreach($products as $id => $values) {
            if ($values->price != 0) {
                $actual_quantity = max(1, $values->quantity);
                $p = Product::find($id);
                $new_price = $values->price / $actual_quantity;
                if ($new_price != $p->price) {
                    $p->price = $new_price;
                    $altered = true;
                    $p->save();
                }
            }
        }

        if ($altered) {
            $this->load('products');
        }
    }

    /*
        Questo serve a determinare quali valori prendere da prodotti e
        prenotazioni a seconda che siano state chieste delle quantità prenotato
        o consegnate
    */
    private function offsetsByStatus($status)
    {
        if ($status == 'delivered') {
            return (object)[
                'alternate' => true,
                'by_variant' => 'delivered',
                'by_product' => 'delivered_pieces',
                'by_booking' => 'delivered',
            ];
        }
        else {
            return (object)[
                'alternate' => false,
                'by_variant' => 'quantity',
                'by_product' => 'quantity_pieces',
                'by_booking' => 'booked',
            ];
        }
    }

    public function formatSummary($fields, $status, $shipping_place)
    {
        $ret = (object) [
            'header' => [],
            'contents' => []
        ];

        $internal_offsets = $this->offsetsByStatus($status);
        $summary = $this->reduxData(null, ['shipping_place' => $shipping_place]);
        $formattable = self::formattableColumns('summary');

        foreach($fields as $f) {
            $ret->headers[] = $formattable[$f]->name;
        }

        foreach ($this->supplier->products as $product) {
            $row = $this->formatProduct($fields, $formattable, $summary->products[$product->id] ?? null, $product, $internal_offsets);
            if (!empty($row)) {
                $ret->contents = array_merge($ret->contents, $row);
            }
        }

        if (in_array('price', $fields)) {
            $row = array_fill(0, count($fields), '');

            $row[0] = _i('Totale');
            $price_offset = array_search('price', $fields);

            if ($status == 'delivered') {
                $row[$price_offset] = printablePrice($summary->price_delivered);
            }
            else {
                $row[$price_offset] = printablePrice($summary->price);
            }

            $ret->contents[] = $row;
        }

        return $ret;
    }

    public function formatShipping($fields, $status, $shipping_place)
    {
        $ret = (object) [
            'headers' => $fields->headers,
            'contents' => [],
        ];

        $formattable_product = self::formattableColumns('shipping');
        $internal_offsets = $this->offsetsByStatus($status);

        $bookings = $this->topLevelBookings(null);
        $bookings = Booking::sortByShippingPlace($bookings, $shipping_place);
        $listed_products = [];

        foreach ($bookings as $booking) {
            $obj = (object) [
                'user_id' => $booking->user->id,

                /*
                    Questi parametri vengono usati per riordinare le
                    prenotazioni rastrellate da diversi ordini, quando genero il
                    documento di Dettaglio Consegne per un aggregato
                */
                'user_sorting' => $booking->user->lastname,
                'gas_sorting' => $booking->user->gas_id,
                'shipping_sorting' => $booking->user->shippingplace ? $booking->user->shippingplace->name : 'AAAA',

                'user' => $booking->user->formattedFields($fields->user_columns),
                'products' => [],
                'totals' => [],
                'notes' => !empty($booking->notes) ? [$booking->notes] : [],
            ];

            foreach($booking->products_with_friends as $booked) {
                if (isset($listed_products[$booked->product_id])) {
                    $product = $listed_products[$booked->product_id];
                    $booked->setRelation('product', $product);
                }
                else {
                    $product = $booked->product;
                    $listed_products[$booked->product_id] = $product;
                }

                $summary = $booked->as_summary;

                $row = $this->formatProduct($fields->product_columns, $formattable_product, $summary->products[$booked->product->id], $product, $internal_offsets);
                if (!empty($row)) {
                    $obj->products = array_merge($obj->products, $row);
                }
            }

            if (empty($obj->products)) {
                continue;
            }

            /*
                All'occorrenza, qui "falsifico" temporaneamente lo stato della
                prenotazione per far tornare i conti in fase di valutazione dei
                modificatori
            */
            $original_booking_status = null;

            if (($booking->status == 'shipped' || $booking->status == 'saved') && $status == 'booked') {
                $original_booking_status = $booking->status;
                $booking->status = 'pending';
            }

            $modifiers = $booking->applyModifiers(null, false);
            $aggregated_modifiers = App\ModifiedValue::aggregateByType($modifiers);
            foreach($aggregated_modifiers as $am) {
                $obj->totals[$am->name] = printablePrice($am->amount);
            }

            $obj->totals['total'] = $booking->getValue($internal_offsets->by_booking, true) + $booking->getValue('modifier:all', true);

            if ($original_booking_status != null) {
                $booking->status = $original_booking_status;
            }

            $ret->contents[] = $obj;
        }

        return $ret;
    }

    public static function formattableColumns($type)
    {
        $ret = [
            'name' => (object) [
                'name' => _i('Nome Prodotto'),
                'checked' => true,
                'format_product' => function($product, $summary) {
                    return $product->printableName();
                },
                'format_variant' => function($product, $summary) {
                    return $product->printableName() . ' - ' . $summary->variant->printableName();
                }
            ],
            'supplier' => (object) [
                'name' => _i('Fornitore'),
                'checked' => false,
                'format_product' => function($product, $summary) {
                    return $product->supplier->printableName();
                },
            ],
            'code' => (object) [
                'name' => _i('Codice Fornitore'),
                'format_product' => function($product, $summary) {
                    return $product->supplier_code;
                },
                'format_variant' => function($product, $summary) {
                    if (!empty($summary->variant->supplier_code)) {
                        return $summary->variant->supplier_code;
                    }
                    else {
                        return $summary->variant->product->product->supplier_code;
                    }
                }
            ],
            'quantity' => (object) [
                'name' => _i('Quantità'),
                'checked' => true,
                'format_product' => function($product, $summary, $alternate = false) {
                    if ($alternate == false)
                        return printableQuantity($summary->quantity_pieces, $product->measure->discrete, 2, ',');
                    else
                        return printableQuantity($summary->delivered_pieces, $product->measure->discrete, 2, ',');
                },
            ],
            'boxes' => (object) [
                'name' => _i('Numero Confezioni'),
                'format_product' => function($product, $summary, $alternate = false) {
                    if ($product->package_size != 0) {
                        if ($alternate == false)
                            return $summary->quantity_pieces / $product->package_size;
                        else
                            return $summary->delivered_pieces / $product->package_size;
                    }
                    else {
                        return '';
                    }
                },
            ],
            'measure' => (object) [
                'name' => _i('Unità di Misura'),
                'checked' => true,
                'format_product' => function($product, $summary, $alternate = false) {
                    if ($alternate == false) {
                        return $product->printableMeasure(true);
                    }
                    else {
                        if ($product->portion_quantity != 0) {
                            return $product->measure->name;
                        }
                        else {
                            return $product->printableMeasure(true);
                        }
                    }
                },
            ],
            'category' => (object) [
                'name' => _i('Categoria'),
                'checked' => false,
                'format_product' => function($product, $summary) {
                    return $product->category ? $product->category->name : '';
                },
            ],
            'unit_price' => (object) [
                'name' => _i('Prezzo Unitario'),
                'checked' => false,
                'format_product' => function($product, $summary) {
                    return printablePrice($product->price, ',');
                },
                'format_variant' => function($product, $summary) {
                    return printablePrice($summary->variant->unitPrice(), ',');
                }
            ],
            'price' => (object) [
                'name' => _i('Prezzo'),
                'checked' => true,
                'format_product' => function($product, $summary, $alternate = false) {
                    if ($alternate == false)
                        return printablePrice($summary->price, ',');
                    else
                        return printablePrice($summary->price_delivered, ',');
                },
            ],
        ];

        if ($type == 'summary') {
            $ret['notes'] = (object) [
                'name' => _i('Note Prodotto'),
                'format_product' => function($product, $summary) {
                    return $product->pivot->notes;
                },
            ];
        }

        return $ret;
    }

    public static function displayColumns()
    {
        return [
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
                'help' => _i('Prezzo unitario (editabile) del prodotto'),
                'width' => 5
            ],
            'available' => (object) [
                'label' => _i('Disponibile'),
                'help' => _i('Quantità disponibile (editabile) del prodotto'),
                'width' => 5
            ],
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
    }

    public static function statuses()
    {
        return [
            'open' => _i('Prenotazioni Aperte'),
            'closed' => _i('Prenotazioni Chiuse'),
            'shipped' => _i('Consegnato'),
            'archived' => _i('Archiviato'),
            'suspended' => _i('In Sospeso'),
        ];
    }

    public function getPermissionsProxies()
    {
        return [$this->supplier];
    }

    public function applyModifiers()
    {
        DB::beginTransaction();

        $modifiers = new Collection();
        $aggregate_data = $this->aggregate->reduxData();

        foreach($this->bookings as $booking) {
            $booking->setRelation('order', $this);
            $modifiers = $modifiers->merge($booking->applyModifiers($aggregate_data));
        }

        DB::rollback();

        return $modifiers;
    }

    /********************************************************* ReducibleTrait */

    protected function reduxBehaviour()
    {
        $ret = $this->emptyReduxBehaviour();

        $ret->children = function($item, $filters) {
            $shipping_place = $filters['shipping_place'] ?? null;
            if ($shipping_place) {
                $bookings = $item->bookings()->whereHas('user', function($query) use ($shipping_place) {
                    $query->where('preferred_delivery_id', $shipping_place);
                });
            }
            else {
                /*
                    Qui recupero solo le prenotazioni di primo livello (non
                    quelle degli amici), in quanto comunque il comportamento di
                    Booking prevede di default di ridurre anche le informazioni
                    degli amici. Se qui contemplassi tutte le prenotazioni,
                    finirei col sommare due volte le quantità degli utenti
                    amici: una volta nella prenotazione stessa, una volta in
                    quella dell'utente superiore.

                    Ricordarsi comunque che qui le prenotazioni vanno sempre
                    lette dal DB, non accedendo al valore eventualmente cachato
                    in $item->bookings. Questo per fare in modo che agendo sullo
                    stesso ordine ma per GAS diversi sia riapplicato lo scope
                    RestrictedGAS, ed ottenere le prenotazioni dell'ordine
                    desiderato; altrimenti, otterrei sempre le prenotazioni del
                    primo GAS che viene elaborato
                */
                $bookings = $item->topLevelBookings();
            }

            return $bookings;
        };

        $ret->optimize = function($item, $child) {
            $child->setRelation('order', $item);
            return $child;
        };

        $ret->collected = 'bookings';
        $ret->merged = 'products';
        return $ret;
    }

    /************************************************************ SluggableID */

    public function getSlugID()
    {
        return sprintf('%s::%s', $this->supplier->id, Str::slug(strftime('%d %B %Y', strtotime($this->start))));
    }

    /******************************************************** CreditableTrait */

    public function getBalanceProxy()
    {
        return $this->supplier;
    }

    public static function balanceFields()
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

    public static function readXML($xml)
    {
        $order = new Order();

        foreach($xml->children() as $p) {
            switch($p->getName()) {
                case 'openDate':
                    $d = html_entity_decode((string) $p);
                    $year = substr($d, 0, 4);
                    $month = substr($d, 4, 2);
                    $day = substr($d, 6, 2);
                    $order->start = sprintf('%d-%d-%d', $year, $month, $day);
                    break;
                case 'closeDate':
                    $d = html_entity_decode((string) $p);
                    $year = substr($d, 0, 4);
                    $month = substr($d, 4, 2);
                    $day = substr($d, 6, 2);
                    $order->end = sprintf('%d-%d-%d', $year, $month, $day);
                    break;
                case 'deliveryDate':
                    $d = html_entity_decode((string) $p);
                    $year = substr($d, 0, 4);
                    $month = substr($d, 4, 2);
                    $day = substr($d, 6, 2);
                    $order->shipping = sprintf('%d-%d-%d', $year, $month, $day);
                    break;
            }
        }

        return $order;
    }

    public function exportJSON()
    {
        $gas = Gas::find($hub->getGas());
        return view('gdxp.json.supplier', ['obj' => $this->supplier, 'order' => $this, 'currentgas' => $gas])->render();
    }

    public static function readJSON($json)
    {
        $order = new Order();
        $order->start = $json->openDate;
        $order->end = $json->closeDate;
        $order->shipping = $json->deliveryDate ?? null;
        return $order;
    }

    /******************************************************** ModifiableTrait */

    public function inheritModificationTypes()
    {
        return $this->supplier;
    }
}
