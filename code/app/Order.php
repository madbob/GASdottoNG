<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

use App;
use Auth;
use DB;
use Mail;
use URL;
use Log;

use App\Events\SluggableCreating;

use App\GASModel;
use App\SluggableID;
use App\BookedProduct;
use App\ExportableTrait;
use App\PayableTrait;
use App\CreditableTrait;
use App\Notifications\NewOrderNotification;

class Order extends Model
{
    use AttachableTrait, ExportableTrait, GASModel, SluggableID, PayableTrait, CreditableTrait;

    public $incrementing = false;

    protected $dispatchesEvents = [
        'creating' => SluggableCreating::class
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('gas', function (Builder $builder) {
            $builder->whereHas('aggregate', function($query) {
                $query->whereHas('gas', function($query) {
                    $user = Auth::user();
                    if (is_null($user))
                        return;
                    $query->where('gas_id', $user->gas->id);
                });
            });
        });
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
        return $this->belongsToMany('App\Product')->with(['variants'])->withPivot('discount_enabled', 'notes')->withTrashed()->orderBy('name');
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

    public function printableName()
    {
        $ret = $this->supplier->name;

        if (!empty($this->comment))
            $ret .= ' - ' . $this->comment;

        $ret .= ' - ' . $this->internal_number;

        return $ret;
    }

    public function printableDates()
    {
        $start = strtotime($this->start);
        $end = strtotime($this->end);
        $string = _i('da %s a %s', [strftime('%A %d %B %G', $start), strftime('%A %d %B %G', $end)]);
        if ($this->shipping != null && $this->shipping != '0000-00-00') {
            $shipping = strtotime($this->shipping);
            $string .= _i(', in consegna %s', strftime('%A %d %B %G', $shipping));
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
        if (is_null($userid)) {
            $userid = Auth::user()->id;
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
        }

        return $ret;
    }

    public function topLevelBookings($status = null)
    {
        $ret = [];

        if (is_null($status))
            $bookings = $this->bookings;
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

    public function getTotalValueAttribute()
    {
        return $this->innerCache('total_value', function($obj) {
            $total_value = 0;

            $bookings_ids = $obj->bookings()->pluck('id');
            $products = BookedProduct::whereIn('booking_id', $bookings_ids)->with(['booking', 'product', 'variants'])->get();
            foreach($products as $booked) {
                $booked->booking->setRelation('order', $obj);
                $total_value += $booked->quantityValue();
            }

            return $total_value;
        });
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

    public function sendNotificationMail()
    {
        if (is_null($this->first_notify) == false) {
            return;
        }

        $order = $this;

        if (currentAbsoluteGas()->getConfig('notify_all_new_orders')) {
            $users = User::all();
        }
        else {
            $users = User::whereHas('suppliers', function($query) use ($order) {
                $query->where('suppliers.id', $order->supplier->id);
            })->get();
        }

        foreach($users as $user) {
            try {
                $user->notify(new NewOrderNotification($order));
            }
            catch(\Exception $e) {
                Log::error('Impossibile inoltrare mail di notifica apertura ordine: ' . $e->getMessage());
            }
        }

        $this->first_notify = date('Y-m-d');
        $this->save();
    }

    public function isActive()
    {
        return $this->status != 'shipped' && $this->status != 'archived';
    }

    public function isRunning()
    {
        return (($this->status == 'open') || ($this->status == 'closed' && $this->keep_open_packages && $this->pendingPackages()->isEmpty() == false));
    }

    public function pendingPackages()
    {
        return $this->innerCache('pending_packages', function($obj) {
            $ret = new Collection();
            $products = $obj->products()->where('package_size', '!=', 0)->with('measure')->get();

            if ($products->isEmpty() == false) {
                $summary = $obj->calculateSummary($products);
                foreach($summary->products as $product_id => $meta)
                    if ($meta['notes'] == true) {
                        /*
                            Se devo completare delle confezioni, altero la quantità
                            massima disponibile a runtime per fare in modo di
                            forzare il raggiungimento della quantità desiderata
                        */

                        $p = $meta['product_obj'];
                        $test = $p->fixed_package_size;

                        $fake_max_available = 0;
                        while($fake_max_available < $meta['quantity']) {
                            $fake_max_available += $test;
                        }

                        $p->max_available = $fake_max_available;

                        $ret->push($p);
                    }
            }

            return $ret;
        });
    }

    public function calculateSummary($products = null, $shipping_place = null)
    {
        $summary = (object) [
            'order' => $this->id,
            'price' => 0,
            'price_delivered' => 0,
            'undiscounted_price' => 0,
            'undiscounted_price_delivered' => 0,
            'products' => [],
            'by_variant' => [],
        ];

        $order = $this;

        if (is_null($products)) {
            /*
                Qui considero i prodotti del fornitore, non solo dell'ordine,
                per calcolare la situazione complessiva compresi i prodotti non
                inclusi nell'ordine stesso
            */
            $products = $order->supplier->products()->with('measure')->get();
            $external_products = false;
        }
        else {
            $external_products = true;
        }

        $total_price = 0;
        $total_price_delivered = 0;
        $total_transport = 0;

        foreach ($products as $product) {
            $q = BookedProduct::with('variants')->with('booking')->where('product_id', '=', $product->id)->whereHas('booking', function ($query) use ($order, $shipping_place) {
                $query->where('order_id', '=', $order->id);
                if ($shipping_place != null) {
                    /*
                        Questa query è formulata per considerare solo il luogo
                        di consegna preferenziale degli utenti "principali", mai
                        degli amici. Ai quali, per qualche motivo, potrebbe
                        essere assegnato un luogo di consegna sbagliato
                    */
                    $query->whereHas('user', function($subquery) use ($shipping_place) {
                        $subquery->where(function($subsubquery) use ($shipping_place) {
                            $subsubquery->where('preferred_delivery_id', $shipping_place)->whereNull('parent_id');
                        })->orWhereHas('parent', function($subsubquery) use ($shipping_place) {
                            $subsubquery->where('preferred_delivery_id', $shipping_place);
                        });
                    });
                }
            });

            $quantity = $q->sum('quantity');
            if(empty($quantity))
                $quantity = 0;

            $delivered = $q->sum('delivered');
            if(empty($delivered))
                $delivered = 0;

            $transport = $quantity * $product->transport;

            $booked = $q->get();
            $price = 0;
            $price_delivered = 0;
            $transport_delivered = 0;
            $variants_quantity = 0;

            foreach ($booked as $b) {
                /*
                    Qui è per agganciare artificiosamente le relazioni con
                    oggetti già caricati.
                    Sia per evitare che vengano ricaricati più volte dal
                    database dalle funzioni interne di calcolo, sia perché se
                    l'array $products è stato esplicitamente passato potrebbe
                    contenere valori (temporanei) da usare al posto di quelli
                    presenti sul database
                */
                $b->setRelation('product', $product);
                $b->booking->setRelation('order', $order);

                $price += $b->quantityValue();
                $price_delivered += $b->final_price;
                $transport_delivered += $b->final_transport;

                if($b->variants->isEmpty() == false) {
                    if(isset($summary->by_variant[$product->id]) == false)
                        $summary->by_variant[$product->id] = [];

                    foreach($b->variants as $v) {
                        $name = $v->printableName();
                        if(isset($summary->by_variant[$product->id][$name]) == false) {
                            $summary->by_variant[$product->id][$name] = [
                                'quantity' => 0,
                                'delivered' => 0,
                                'price' => 0,
                                'unit_price' => $v->unitPrice()
                            ];
                        }

                        $summary->by_variant[$product->id][$name]['quantity'] += $v->quantity;
                        $summary->by_variant[$product->id][$name]['delivered'] += $v->delivered;
                        $summary->by_variant[$product->id][$name]['price'] += $v->quantityValue();

                        $variants_quantity += $v->quantity;
                    }
                }
            }

            /*
                In presenza di varianti, ricalcolo la quantità totale come somma
                delle loro effettive quantità. Questo per evitare discrepanze
                tra la quantità salvata nel prodotto ordinato di riferimento e,
                appunto, le varianti collegate
            */
            if ($variants_quantity != 0)
                $quantity = $variants_quantity;

            if ($product->portion_quantity > 0) {
                $quantity_pieces = $quantity;
                $delivered_pieces = $delivered;
                $quantity = $quantity * $product->portion_quantity;
                $delivered = $delivered;
            }
            else {
                $quantity_pieces = $quantity;
                $delivered_pieces = $delivered;
            }

            $summary->products[$product->id]['product_obj'] = $product;
            $summary->products[$product->id]['quantity'] = printableQuantity($quantity, $product->measure->discrete);
            $summary->products[$product->id]['quantity_pieces'] = $quantity_pieces;
            $summary->products[$product->id]['price'] = printablePrice($price);
            $summary->products[$product->id]['transport'] = printablePrice($transport);
            $summary->products[$product->id]['delivered'] = printableQuantity($delivered, $product->measure->discrete, 3);
            $summary->products[$product->id]['delivered_pieces'] = $delivered_pieces;
            $summary->products[$product->id]['price_delivered'] = printablePrice($price_delivered);
            $summary->products[$product->id]['transport_delivered'] = printablePrice($transport_delivered);

            $total_price += $price;
            $total_price_delivered += $price_delivered;
            $total_transport += $transport;

            $summary->products[$product->id]['notes'] = false;
            if ($product->package_size != 0 && $quantity != 0) {
                $test = round(fmod($quantity, $product->fixed_package_size));
                if ($test != 0) {
                    $summary->products[$product->id]['notes'] = true;
                }
            }
        }

        $summary->undiscounted_price = $total_price;
        $summary->undiscounted_price_delivered = $total_price_delivered;
        $summary->price = applyPercentage($summary->undiscounted_price, $this->discount);
        $summary->price_delivered = applyPercentage($summary->undiscounted_price_delivered, $this->discount);

        /*
            Il prezzo del trasporto è la somma del prezzo di trasporto di tutti
            i prodotti con il prezzo di trasporto globale di tutto l'ordine.
            Solitamente solo uno dei due è valorizzato, ma per buona misura li
            metto insieme
        */
        $summary->transport = $total_transport + applyPercentage($summary->price, $order->transport, '=');

        $total_transport_delivered = 0;
        foreach ($order->bookings()->where('status', 'shipped')->get() as $shipped_booking)
            $total_transport_delivered += $shipped_booking->getValue('transport', true);
        $summary->transport_delivered = $total_transport_delivered;

        $summary->notes = [];
        foreach ($order->bookings()->where('notes', '!=', '')->get() as $annotated_booking) {
            $summary->notes[] = (object) [
                'user' => $annotated_booking->user->printableName(),
                'note' => $annotated_booking->notes
            ];
        }

        return $summary;
    }

    public function calculateInvoicingSummary($products = null)
    {
        $summary = (object) [
            'order' => $this->id,
            'total' => 0,
            'total_taxable' => 0,
            'total_tax' => 0,
            'transport' => 0,
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
        $transport = 0;
        $rates = [];

        foreach ($products as $product) {
            $query = BookedProduct::with('variants')->with('booking')->where('product_id', '=', $product->id)->whereHas('booking', function ($query) use ($order) {
                $query->where('order_id', '=', $order->id);
            });

            $price_delivered = $query->sum('final_price') - $query->sum('final_discount');
            $transport += $query->sum('final_transport');
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

        $summary->transport = $transport;
        $summary->total = printablePrice($global_total + $summary->transport);
        $summary->total_taxable = printablePrice($global_total_taxable);
        $summary->total_tax = printablePrice($global_total_tax);

        return $summary;
    }

    private function formatProduct($fields, $formattable, $summary, $product, $internal_offsets)
    {
        if(isset($summary->by_variant[$product->id])) {
            $variants_rows = [];

            foreach ($summary->by_variant[$product->id] as $name => $variant) {
                if ($variant[$internal_offsets->by_variant] == 0) {
                    continue;
                }

                $row = [];
                foreach($fields as $f) {
                    if (isset($formattable[$f])) {
                        $row[] = call_user_func($formattable[$f]->format_variant, $product, $summary, $name, $variant, $internal_offsets->alternate);
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
            if ($summary->products[$product->id][$internal_offsets->by_product] == 0) {
                return [];
            }

            $row = [];
            foreach($fields as $f) {
                if (isset($formattable[$f])) {
                    $row[] = call_user_func($formattable[$f]->format_product, $product, $summary, $internal_offsets->alternate);
                }
            }

            return [$row];
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
        $summary = $this->calculateSummary(null, $shipping_place);
        $formattable = self::formattableColumns('summary');

        foreach($fields as $f) {
            $ret->headers[] = $formattable[$f]->name;
        }

        foreach ($this->supplier->products as $product) {
            if($this->hasProduct($product) == false)
                continue;

            $row = $this->formatProduct($fields, $formattable, $summary, $product, $internal_offsets);
            if (!empty($row)) {
                $ret->contents = array_merge($ret->contents, $row);
            }
        }

        if (in_array('price', $fields) || in_array('transport', $fields)) {
            $row = array_fill(0, count($fields), '');

            $row[0] = _i('Totale');
            $price_offset = array_search('price', $fields);
            $transport_offset = array_search('transport', $fields);

            if ($status == 'delivered') {
                $order = $this;

                if ($price_offset !== false) {
                    $row[$price_offset] = BookedProduct::whereHas('booking', function($query) use ($order) {
                        $query->where('order_id', $order->id);
                    })->sum('final_price');
                }

                if ($transport_offset !== false) {
                    $row[$transport_offset] = BookedProduct::whereHas('booking', function($query) use ($order) {
                        $query->where('order_id', $order->id);
                    })->sum('final_transport');
                }
            }
            else {
                if ($price_offset !== false)
                    $row[$price_offset] = printablePrice($summary->price, ',');

                if ($transport_offset !== false)
                    $row[$transport_offset] = printablePrice($summary->transport, ',');
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
                'user' => $booking->user->formattedFields($fields->user_columns),
                'products' => [],
                'totals' => [],
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

                $row = $this->formatProduct($fields->product_columns, $formattable_product, $summary, $product, $internal_offsets);
                if (!empty($row)) {
                    $obj->products = array_merge($obj->products, $row);
                }
            }

            if (empty($obj->products)) {
                continue;
            }

            $obj->totals['total'] = $booking->getValue($internal_offsets->by_booking, true);
            $obj->totals['transport'] = $booking->getValue('transport', true);
            $obj->totals['discount'] = $booking->getValue('discount', true);

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
                'format_variant' => function($product, $summary, $name, $variant) {
                    return $product->printableName() . ' - ' . $name;
                }
            ],
            'supplier' => (object) [
                'name' => _i('Fornitore'),
                'checked' => false,
                'format_product' => function($product, $summary) {
                    return $product->supplier->printableName();
                },
                'format_variant' => function($product, $summary, $name, $variant) {
                    return $product->supplier->printableName();
                }
            ],
            'code' => (object) [
                'name' => _i('Codice Fornitore'),
                'format_product' => function($product, $summary) {
                    return $product->supplier_code;
                },
                'format_variant' => function($product, $summary, $name, $variant) {
                    /*
                        TODO: le varianti hanno un loro proprio codice?
                    */
                    return $product->supplier_code;
                }
            ],
            'quantity' => (object) [
                'name' => _i('Quantità'),
                'checked' => true,
                'format_product' => function($product, $summary, $alternate = false) {
                    if ($alternate == false)
                        return printableQuantity($summary->products[$product->id]['quantity_pieces'], $product->measure->discrete, 2, ',');
                    else
                        return printableQuantity($summary->products[$product->id]['delivered_pieces'], $product->measure->discrete, 2, ',');
                },
                'format_variant' => function($product, $summary, $name, $variant, $alternate = false) {
                    if ($alternate == false)
                        return printableQuantity($variant['quantity'], $product->measure->discrete, 2, ',');
                    else
                        return printableQuantity($variant['delivered'], $product->measure->discrete, 2, ',');
                }
            ],
            'boxes' => (object) [
                'name' => _i('Numero Confezioni'),
                'format_product' => function($product, $summary, $alternate = false) {
                    if ($product->package_size != 0) {
                        if ($alternate == false)
                            return $summary->products[$product->id]['quantity_pieces'] / $product->package_size;
                        else
                            return $summary->products[$product->id]['delivered_pieces'] / $product->package_size;
                    }
                    else {
                        return '';
                    }
                },
                'format_variant' => function($product, $summary, $name, $variant, $alternate = false) {
                    if ($product->package_size != 0) {
                        if ($alternate == false)
                            return $variant['quantity'] / $product->package_size;
                        else
                            return $variant['delivered'] / $product->package_size;
                    }
                    else {
                        return '';
                    }
                }
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
                'format_variant' => function($product, $summary, $name, $variant, $alternate = false) {
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
                }
            ],
            'category' => (object) [
                'name' => _i('Categoria'),
                'checked' => false,
                'format_product' => function($product, $summary) {
                    return $product->category ? $product->category->name : '';
                },
                'format_variant' => function($product, $summary, $name, $variant) {
                    return $product->category ? $product->category->name : '';
                }
            ],
            'unit_price' => (object) [
                'name' => _i('Prezzo Unitario'),
                'checked' => false,
                'format_product' => function($product, $summary) {
                    return printablePrice($product->price, ',');
                },
                'format_variant' => function($product, $summary, $name, $variant) {
                    return printablePrice($variant['unit_price'], ',');
                }
            ],
            'price' => (object) [
                'name' => _i('Prezzo'),
                'checked' => true,
                'format_product' => function($product, $summary, $alternate = false) {
                    if ($alternate == false)
                        return printablePrice($summary->products[$product->id]['price'], ',');
                    else
                        return printablePrice($summary->products[$product->id]['price_delivered'], ',');
                },
                'format_variant' => function($product, $summary, $name, $variant, $alternate = false) {
                    if ($alternate == false)
                        return printablePrice($variant['price'], ',');
                    else
                        return 0;
                }
            ],
        ];

        if ($type == 'summary') {
            $ret['transport'] = (object) [
                'name' => _i('Prezzo Trasporto'),
                'format_product' => function($product, $summary, $alternate = false) {
                    if ($alternate == false)
                        return printablePrice($summary->products[$product->id]['transport'], ',');
                    else
                        return printablePrice($summary->products[$product->id]['transport_delivered'], ',');
                },
                'format_variant' => function($product, $summary, $name, $variant, $alternate = false) {
                    if ($alternate == false)
                        return printablePrice($summary->products[$product->id]['transport'], ',');
                    else
                        return 0;
                }
            ];

            $ret['notes'] = (object) [
                'name' => _i('Note Prodotto'),
                'format_product' => function($product, $summary) {
                    return $product->pivot->notes;
                },
                'format_variant' => function($product, $summary, $name, $variant) {
                    return $product->pivot->notes;
                }
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
                'width' => 8
            ],
            'transport' => (object) [
                'label' => _i('Trasporto'),
                'help' => _i('Prezzo di trasporto unitario (editabile) del prodotto'),
                'width' => 8
            ],
            'available' => (object) [
                'label' => _i('Disponibile'),
                'help' => _i('Quantità disponibile (editabile) del prodotto'),
                'width' => 8
            ],
            'discount' => (object) [
                'label' => _i('Sconto'),
                'help' => _i('Per abilitare o disabilitare lo sconto unitario del prodotto'),
                'width' => 4
            ],
            'unit_measure' => (object) [
                'label' => _i('Unità di Misura'),
                'help' => _i('Unità di misura assegnata al prodotto'),
                'width' => 9
            ],
            'quantity' => (object) [
                'label' => _i('Quantità Prenotata'),
                'help' => _i('Quantità complessivamente prenotata del prodotto'),
                'width' => 9
            ],
            'total_price' => (object) [
                'label' => _i('Totale Prezzo'),
                'help' => _i('Totale prezzo della quantità prenotata'),
                'width' => 5
            ],
            'total_transport' => (object) [
                'label' => _i('Totale Trasporto'),
                'help' => _i('Totale del prezzo di trasporto. Significativo solo quando è applicato un prezzo di trasporto unitario sul prodotto'),
                'width' => 5
            ],
            'quantity_delivered' => (object) [
                'label' => _i('Quantità Consegnata'),
                'help' => _i('Quantità complessivamente consegnata del prodotto'),
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
                'width' => 7
            ],
        ];
    }

    public function getPermissionsProxies()
    {
        return [$this->supplier];
    }

    /************************************************************ SluggableID */

    public function getSlugID()
    {
        return sprintf('%s::%s', $this->supplier->id, str_slug(strftime('%d %B %G', strtotime($this->start))));
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
        return view('gdxp.json.supplier', ['obj' => $this->supplier, 'orders' => [$this]])->render();
    }

    public static function readJSON($json)
    {
        $order = new Order();
        $order->start = $json->openDate;
        $order->end = $json->closeDate;
        $order->shipping = $json->deliveryDate ?? null;
        $order->transport = $json->shippingCost ?? 0;
        return $order;
    }
}
