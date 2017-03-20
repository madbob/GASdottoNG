<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\GASModel;
use App\SluggableID;
use App\BookedProduct;

class Order extends Model
{
    use AttachableTrait, GASModel, SluggableID, PayableTrait;

    public $incrementing = false;

    public function supplier()
    {
        return $this->belongsTo('App\Supplier');
    }

    public function aggregate()
    {
        return $this->belongsTo('App\Aggregate');
    }

    public function products()
    {
        return $this->belongsToMany('App\Product')->with('measure')->with('category')->with('variants')->withPivot('discount_enabled');
    }

    public function bookings()
    {
        return $this->hasMany('App\Booking')->with('user');
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
        $string = sprintf('da %s a %s', strftime('%A %d %B %G', $start), strftime('%A %d %B %G', $end));
        if ($this->shipping != null && $this->shipping != '0000-00-00') {
            $shipping = strtotime($this->shipping);
            $string .= sprintf(', in consegna %s', strftime('%A %d %B %G', $shipping));
        }

        return $string;
    }

    public function userBooking($userid = null)
    {
        if ($userid == null) {
            $userid = Auth::user()->id;
        }

        $ret = $this->hasMany('App\Booking')->whereHas('user', function ($query) use ($userid) {
            $query->where('id', '=', $userid);
        })->first();

        if ($ret == null) {
            $b = new Booking();
            $b->user_id = $userid;
            $b->order_id = $this->id;
            $b->status = 'pending';

            return $b;
        } else {
            return $ret;
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

    public function calculateSummary()
    {
        $summary = (object) [
            'order' => $this->id,
            'price' => 0,
            'products' => [],
        ];

        $order = $this;
        $products = $order->supplier->products;
        $total_price = 0;
        $total_price_delivered = 0;
        $total_transport = 0;

        foreach ($products as $product) {
            $q = BookedProduct::where('product_id', '=', $product->id)->whereHas('booking', function ($query) use ($order) {
                $query->where('order_id', '=', $order->id);
            });

            $quantity = $q->sum('quantity');
            $delivered = $q->sum('delivered');
            $base_price = $product->contextualPrice($order);
            $transport = $quantity * $product->transport;

            $booked = $q->get();
            $price = 0;
            $price_delivered = 0;

            foreach ($booked as $b) {
                $price += $b->quantityValue();
                $price_delivered += $b->deliveredValue();
            }

            $summary->products[$product->id]['quantity'] = $quantity ? $quantity : 0;
            $summary->products[$product->id]['price'] = $price;
            $summary->products[$product->id]['transport'] = $transport;
            $summary->products[$product->id]['delivered'] = $delivered ? $delivered : 0;
            $summary->products[$product->id]['price_delivered'] = $price_delivered;

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
        $summary->transport = $total_transport;

        return $summary;
    }

    protected function defaultAttachments()
    {
        /*
            Documento con i prodotti e le relative quantità totali.
            Solitamente destinato al fornitore, come riassunto
            dell'ordine complessivo
        */
        $summary = new Attachment();
        $summary->name = 'Riassunto Prodotti';
        $summary->url = url('orders/document/'.$this->id.'/summary');
        $summary->internal = true;

        /*
            Rappresentazione strutturata delle prenotazioni
            effettuate, da usare in fase di consegna
        */
        $shipping = new Attachment();
        $shipping->name = 'Dettaglio Consegne';
        $shipping->url = url('orders/document/'.$this->id.'/shipping');
        $shipping->internal = true;

        /*
            CVS completo dei prodotti, degli utenti e delle quantità
        */
        $table = new Attachment();
        $table->name = 'Tabella Complessiva';
        $table->url = url('orders/document/'.$this->id.'/table');
        $table->internal = true;

        return [$shipping, $summary, $table];
    }
}
