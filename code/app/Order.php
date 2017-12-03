<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

use Auth;
use DB;
use URL;
use Theme;

use App\Events\SluggableCreating;
use App\GASModel;
use App\SluggableID;
use App\BookedProduct;
use App\ExportableTrait;

class Order extends Model
{
    use AttachableTrait, ExportableTrait, GASModel, SluggableID, PayableTrait;

    public $incrementing = false;

    protected $events = [
        'creating' => SluggableCreating::class
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('gas', function (Builder $builder) {
            $builder->whereHas('aggregate', function($query) {
                $query->whereHas('gas', function($query) {
                    $user = Auth::user();
                    if ($user == null)
                        return;
                    $query->where('gas_id', $user->gas->id);
                });
            });
        });
    }

    public static function commonClassName()
    {
        return 'Ordine';
    }

    public function supplier()
    {
        return $this->belongsTo('App\Supplier')->withTrashed();
    }

    public function aggregate()
    {
        return $this->belongsTo('App\Aggregate');
    }

    public function products()
    {
        return $this->belongsToMany('App\Product')->with('measure')->with('category')->with('variants')->withPivot('discount_enabled')->withTrashed()->orderBy('name');
    }

    public function bookings()
    {
        return $this->hasMany('App\Booking')->with('user')->with('products')->sorted();
    }

    public function payment()
    {
        return $this->belongsTo('App\Movement');
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
                $ret .= '<span class="glyphicototal_valuen glyphicon-'.$i.'" aria-hidden="true"></span>&nbsp;';
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
        $string = sprintf('da %s a %s', strftime('%A %d %B %G', $start), strftime('%A %d %B %G', $end));
        if ($this->shipping != null && $this->shipping != '0000-00-00') {
            $shipping = strtotime($this->shipping);
            $string .= sprintf(', in consegna %s', strftime('%A %d %B %G', $shipping));
        }

        return $string;
    }

    public function getBookingURL()
    {
        return URL::action('BookingController@index').'#' . $this->aggregate->id;
    }

    public function userBooking($userid = null, $fallback = true)
    {
        if ($userid == null) {
            $userid = Auth::user()->id;
        }

        $ret = $this->hasMany('App\Booking')->whereHas('user', function ($query) use ($userid) {
            $query->where('id', '=', $userid);
        })->first();

        if ($ret == null && $fallback == true) {
            $ret = new Booking();
            $ret->user_id = $userid;
            $ret->order_id = $this->id;
            $ret->status = 'pending';
        }

        return $ret;
    }

    public function getInternalNumberAttribute()
    {
        if(!isset($this->internal_number_cache)) {
            $o = $this;
            $year = date('Y', strtotime($o->start));

            $this->internal_number_cache = (Order::where(DB::raw('YEAR(start)'), $year)->where(function($query) use ($o) {
                $query->where('start', '<', $this->start)->orWhere(function($query) use ($o) {
                    $query->where('start', $this->start)->where('id', '<', $this->id);
                });
            })->count() + 1) . '/' . $year;
        }

        return $this->internal_number_cache;
    }

    public function getTotalValueAttribute()
    {
        return $this->innerCache('total_value', function($obj) {
            $total_value = 0;

            $bookings_ids = $obj->bookings->pluck('id');
            $products = BookedProduct::whereIn('booking_id', $bookings_ids)->with('booking')->with('product')->with('variants')->get();
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
            'products' => [],
            'by_variant' => [],
        ];

        $order = $this;

        if ($products == null) {
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

        $summary->price = $total_price;
        $summary->price_delivered = $total_price_delivered;

        /*
            Il prezzo del trasporto è la somma del prezzo di trasporto di tutti
            i prodotti con il prezzo di trasporto globale di tutto l'ordine.
            Solitamente solo uno dei due è valorizzato, ma per buona misura li
            metto insieme
        */
        $summary->transport = $total_transport + $order->transport;

        $total_transport_delivered = 0;
        if ($order->transport > 0) {
            foreach ($order->bookings()->where('status', 'shipped')->get() as $shipped_booking)
                $total_transport_delivered += $shipped_booking->transport;
        }
        $summary->transport_delivered = $total_transport_delivered;

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
                    'name' => 'Nome Prodotto',
                    'checked' => true,
                    'format_product' => function($product, $summary) {
                        return $product->printableName();
                    },
                    'format_variant' => function($product, $summary, $name, $variant) {
                        return $product->printableName() . ' - ' . $name;
                    }
                ],
                'code' => (object) [
                    'name' => 'Codice Fornitore',
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
                    'name' => 'Quantità Totale',
                    'checked' => true,
                    'format_product' => function($product, $summary) {
                        return printableQuantity($summary->products[$product->id]['quantity_pieces'], $product->measure->discrete, 2, ',');
                    },
                    'format_variant' => function($product, $summary, $name, $variant) {
                        return printableQuantity($variant['quantity'], $product->measure->discrete, 2, ',');
                    }
                ],
                'boxes' => (object) [
                    'name' => 'Numero Confezioni',
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
                    'name' => 'Unità di Misura',
                    'checked' => true,
                    'format_product' => function($product, $summary) {
                        return $product->printableMeasure(true);
                    },
                    'format_variant' => function($product, $summary, $name, $variant) {
                        return $product->printableMeasure(true);
                    }
                ],
                'price' => (object) [
                    'name' => 'Prezzo Totale',
                    'checked' => true,
                    'format_product' => function($product, $summary) {
                        return printablePrice($summary->products[$product->id]['price'], ',');
                    },
                    'format_variant' => function($product, $summary, $name, $variant) {
                        return printablePrice($variant['price'], ',');
                    }
                ],
                'transport' => (object) [
                    'name' => 'Prezzo Trasporto',
                    'format_product' => function($product, $summary) {
                        return printablePrice($summary->products[$product->id]['transport'], ',');
                    },
                    'format_variant' => function($product, $summary, $name, $variant) {
                        return printablePrice($summary->products[$product->id]['transport'], ',');
                    }
                ],
            ];
        }
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
            'bank' => 'Saldo',
        ];
    }

    /******************************************************** ExportableTrait */

    public function exportXML()
    {
        return Theme::view('gdxp.supplier', ['obj' => $this->supplier, 'orders' => [$this]])->render();
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
