<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

use App;
use Auth;
use DB;
use URL;

use App\Events\SluggableCreating;
use App\GASModel;
use App\SluggableID;
use App\BookedProduct;
use App\ExportableTrait;
use App\PayableTrait;
use App\CreditableTrait;

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

    public function getSlugID()
    {
        return sprintf('%s::%s', $this->supplier->id, str_slug(strftime('%d %B %G', strtotime($this->start))));
    }

    public function printableName()
    {
        $ret = $this->supplier->name;

        if (!empty($this->comment))
            $ret .= ' - ' . $this->comment;

        $ret .= ' - ' . $this->internal_number;

        return $ret;
    }

    public function printableHeader()
    {
        $ret = $this->printableName();
        $icons = $this->icons();

        if (!empty($icons)) {
            $ret .= '<div class="pull-right">';

            foreach ($icons as $i) {
                $ret .= '<span class="glyphicon glyphicon-'.$i.'" aria-hidden="true"></span>&nbsp;';
            }

            $ret .= '</div>';
        }

        $ret .= sprintf('<br/><small>%s</small>', $this->printableDates());

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

    public function isActive()
    {
        return $this->status != 'shipped' && $this->status != 'archived';
    }

    public function isRunning()
    {
        return $this->status == 'open';
    }

    public function calculateSummary($products = null)
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
            $products = $order->supplier->products;
            $external_products = false;
        }
        else {
            $external_products = true;
        }

        $total_price = 0;
        $total_price_delivered = 0;
        $total_transport = 0;

        foreach ($products as $product) {
            $q = BookedProduct::with('variants')->with('booking')->where('product_id', '=', $product->id)->whereHas('booking', function ($query) use ($order) {
                $query->where('order_id', '=', $order->id);
            });

            $quantity = $q->sum('quantity');
            if(!$quantity)
                $quantity = 0;

            $delivered = $q->sum('delivered');
            if(!$delivered)
                $delivered = 0;

            $transport = $quantity * $product->transport;

            $booked = $q->get();
            $price = 0;
            $price_delivered = 0;

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

                if($b->variants->isEmpty() == false) {
                    if(isset($summary->by_variant[$product->id]) == false)
                        $summary->by_variant[$product->id] = [];

                    foreach($b->variants as $v) {
                        $name = $v->printableName();
                        if(isset($summary->by_variant[$product->id][$name]) == false) {
                            $summary->by_variant[$product->id][$name] = [
                                'quantity' => 0,
                                'price' => 0
                            ];
                        }

                        $summary->by_variant[$product->id][$name]['quantity'] += $v->quantity;
                        $summary->by_variant[$product->id][$name]['price'] += $v->quantityValue();
                    }
                }
            }

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

            $summary->products[$product->id]['quantity'] = printableQuantity($quantity, $product->measure->discrete);
            $summary->products[$product->id]['quantity_pieces'] = $quantity_pieces;
            $summary->products[$product->id]['price'] = printablePrice($price);
            $summary->products[$product->id]['transport'] = printablePrice($transport);
            $summary->products[$product->id]['delivered'] = printableQuantity($delivered, $product->measure->discrete, 3);
            $summary->products[$product->id]['delivered_pieces'] = $delivered_pieces;
            $summary->products[$product->id]['price_delivered'] = printablePrice($price_delivered);

            $total_price += $price;
            $total_price_delivered += $price_delivered;
            $total_transport += $transport;

            $summary->products[$product->id]['notes'] = false;
            if ($product->package_size != 0 && $quantity != 0) {
                if ($product->portion_quantity <= 0) {
                    $test = $product->package_size;
                } else {
                    $test = round($product->portion_quantity * $product->package_size, 2);
                }

                $test = round($quantity % $test);
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
        $summary->transport = $total_transport + $order->transport;

        $total_transport_delivered = 0;
        foreach ($order->bookings()->where('status', 'shipped')->get() as $shipped_booking)
            $total_transport_delivered += $shipped_booking->transport;
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

        if ($products == null) {
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
            $price_delivered = BookedProduct::with('variants')->with('booking')->where('product_id', '=', $product->id)->whereHas('booking', function ($query) use ($order) {
                $query->where('order_id', '=', $order->id);
            })->sum('final_price');

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

            $global_total += $price_delivered;
            $global_total_taxable += $total;
            $global_total_tax += $total_vat;
        }

        $summary->total = printablePrice($global_total);
        $summary->total_taxable = printablePrice($global_total_taxable);
        $summary->total_tax = printablePrice($global_total_tax);

        $transport = 0;
        foreach ($order->bookings()->where('status', 'shipped')->get() as $shipped_booking)
            $transport += $shipped_booking->transport;
        $summary->transport = $transport;

        return $summary;
    }

    public function formatSummary($fields)
    {
        $ret = (object) [
            'header' => [],
            'contents' => []
        ];

        $summary = $this->calculateSummary();
        $formattable = self::formattableColumns('summary');

        foreach($fields as $f) {
            $ret->headers[] = $formattable[$f]->name;
        }

        foreach ($this->supplier->products as $product) {
            if($this->hasProduct($product) == false)
                continue;

            if(isset($summary->by_variant[$product->id])) {
                $variants_rows = [];

                foreach ($summary->by_variant[$product->id] as $name => $variant) {
                    if ($variant['quantity'] == 0)
                        continue;

                    $row = [];
                    foreach($fields as $f) {
                        $row[] = call_user_func($formattable[$f]->format_variant, $product, $summary, $name, $variant);
                    }

                    $variants_rows[] = $row;
                }

                usort($variants_rows, function($a, $b) {
                    return $a[0] <=> $b[0];
                });

                $ret->contents = array_merge($ret->contents, $variants_rows);
            }
            else {
                if ($summary->products[$product->id]['quantity_pieces'] == 0)
                    continue;

                $row = [];
                foreach($fields as $f) {
                    $row[] = call_user_func($formattable[$f]->format_product, $product, $summary);
                }

                $ret->contents[] = $row;
            }
        }

        return $ret;
    }

    public static function formattableColumns($type)
    {
        if ($type == 'summary') {
            return [
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
                    'name' => _i('Quantità Totale'),
                    'checked' => true,
                    'format_product' => function($product, $summary) {
                        return printableQuantity($summary->products[$product->id]['quantity_pieces'], $product->measure->discrete, 2, ',');
                    },
                    'format_variant' => function($product, $summary, $name, $variant) {
                        return printableQuantity($variant['quantity'], $product->measure->discrete, 2, ',');
                    }
                ],
                'boxes' => (object) [
                    'name' => _i('Numero Confezioni'),
                    'format_product' => function($product, $summary) {
                        if ($product->package_size != 0)
                            return $summary->products[$product->id]['quantity_pieces'] / $product->package_size;
                        else
                            return '';
                    },
                    'format_variant' => function($product, $summary, $name, $variant) {
                        if ($product->package_size != 0)
                            return $variant['quantity'] / $product->package_size;
                        else
                            return '';
                    }
                ],
                'measure' => (object) [
                    'name' => _i('Unità di Misura'),
                    'checked' => true,
                    'format_product' => function($product, $summary) {
                        return $product->printableMeasure(true);
                    },
                    'format_variant' => function($product, $summary, $name, $variant) {
                        return $product->printableMeasure(true);
                    }
                ],
                'price' => (object) [
                    'name' => _i('Prezzo Totale'),
                    'checked' => true,
                    'format_product' => function($product, $summary) {
                        return printablePrice($summary->products[$product->id]['price'], ',');
                    },
                    'format_variant' => function($product, $summary, $name, $variant) {
                        return printablePrice($variant['price'], ',');
                    }
                ],
                'transport' => (object) [
                    'name' => _i('Prezzo Trasporto'),
                    'format_product' => function($product, $summary) {
                        return printablePrice($summary->products[$product->id]['transport'], ',');
                    },
                    'format_variant' => function($product, $summary, $name, $variant) {
                        return printablePrice($summary->products[$product->id]['transport'], ',');
                    }
                ],
                'notes' => (object) [
                    'name' => _i('Note Prodotto'),
                    'format_product' => function($product, $summary) {
                        return $product->pivot->notes;
                    },
                    'format_variant' => function($product, $summary, $name, $variant) {
                        return $product->pivot->notes;
                    }
                ],
            ];
        }
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

    /******************************************************** CreditableTrait */

    public function alterBalance($amount, $type = 'bank')
    {
        $this->supplier->alterBalance($amount, $type);
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
        return view('gdxp.supplier', ['obj' => $this->supplier, 'orders' => [$this]])->render();
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
}
