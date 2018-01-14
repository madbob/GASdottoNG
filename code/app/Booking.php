<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use DB;
use URL;

use App\Events\SluggableCreating;
use App\Events\BookingDeleting;
use App\GASModel;
use App\SluggableID;
use App\BookedProduct;

class Booking extends Model
{
    use GASModel, SluggableID, PayableTrait;

    public $incrementing = false;

    protected $events = [
        'creating' => SluggableCreating::class,
        'deleting' => BookingDeleting::class,
    ];

    public static function commonClassName()
    {
        return 'Prenotazione';
    }

    public function user()
    {
        return $this->belongsTo('App\User')->withTrashed();
    }

    public function order()
    {
        return $this->belongsTo('App\Order');
    }

    public function supplier()
    {
        return $this->order->supplier;
    }

    public function products()
    {
        return $this->hasMany('App\BookedProduct')->whereHas('product', function ($query) {
            $query->orderBy('name', 'asc');
        })->with('variants');
    }

    public function deliverer()
    {
        return $this->belongsTo('App\User', 'deliverer_id');
    }

    public function payment()
    {
        return $this->belongsTo('App\Movement');
    }

    public function scopeSorted($query)
    {
        $sorted_users = "'" . join("', '", User::withTrashed()->sorted()->pluck('id')->toArray()) . "'";
        return $query->orderByRaw(DB::raw("FIELD(user_id, $sorted_users)"));
    }

    public function scopeToplevel($query)
    {
        $query->whereHas('user', function($query) {
            $query->where('parent_id', null);
        });
    }

    public function getBooked($product_id, $fallback = false)
    {
        if (is_object($product_id)) {
            $product_id = $product_id->id;
        }

        $p = $this->products()->whereHas('product', function ($query) use ($product_id) {
            $query->where('id', '=', $product_id);
        })->first();

        if ($p == null && $fallback == true) {
            $p = new BookedProduct();
            $p->booking_id = $this->id;
            $p->product_id = $product_id;
        }

        return $p;
    }

    private function readProductQuantity($product, $field, $friends_bookings)
    {
        $p = $this->getBooked($product);

        if ($p == null)
            $ret = 0;
        else
            $ret = $p->$field;

        if ($friends_bookings) {
            foreach ($this->friends_bookings as $sub)
                $ret += $sub->readProductQuantity($product, $field, false);
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
        return $this->innerCache('value', function($obj) {
            $value = 0;

            foreach ($obj->products as $booked) {
                $value += $booked->quantityValue();
            }

            return $value;
        });
    }

    /*
        Valore complessivo di quanto consegnato
    */
    public function getDeliveredAttribute()
    {
        $sum = 0;

        foreach ($this->products as $booked) {
            $sum += $booked->final_price;
        }

        return $sum;
    }

    /*
        Questo ritorna solo il costo di trasporto applicato sull'ordine
        complessivo
    */
    public function getMajorTransportAttribute()
    {
        if($this->order->transport > 0) {
            $total_value = $this->order->total_value;
            if ($total_value != 0) {
                if (is_numeric($this->order->transport)) {
                    return round($this->value * $this->order->transport / $total_value, 2);
                }
                else {
                    return $this->value - applyPercentage($this->value, $this->order->transport);
                }
            }
        }

        return 0;
    }

    /*
        Se la prenotazione non è ancora stata consegnata, restituisce il costo
        di trasporto sull'ordine complessivo (cfr. getMajorTransportAttribute())
        sommato alla somma di trasporto dei singoli prodotti già consegnati.
        Se la prenotazione è stata consegnata, restituisce il valore salvato sul
        database
    */
    public function getCheckTransportAttribute()
    {
        if ($this->status == 'shipped') {
            return $this->transport;
        }
        else {
            return $this->innerCache('transport', function($obj) {
                $transport = $obj->major_transport;

                foreach($this->products as $p) {
                    $transport += $p->transportDeliveredValue();
                }

                return $transport;
            });
        }
    }

    /*
        Questo ritorna solo lo sconto applicato sull'ordine complessivo
    */
    public function getMajorDiscountAttribute()
    {
        if(!empty($this->order->discount)) {
            $total_value = $this->order->total_value;
            if ($total_value != 0) {
                if (is_numeric($this->order->discount)) {
                    return round($this->value * $this->order->discount / $total_value, 2);
                }
                else {
                    return $this->value - applyPercentage($this->value, $this->order->discount);
                }
            }
        }

        return 0;
    }

    /*
        Valore complessivo di quanto ordinato + spedizione globale
    */
    public function getTotalValueAttribute()
    {
        return $this->value + $this->check_transport - $this->major_discount;
    }

    /*
        Valore complessivo di quanto consegnato + spedizione globale
    */
    public function getTotalDeliveredAttribute()
    {
        return $this->delivered + $this->transport;
    }

    public function getProductsWithFriendsAttribute()
    {
        $products = $this->products;

        foreach($this->friends_bookings as $sub) {
            foreach($sub->products as $sub_p) {
                $master_p = $products->keyBy('product_id')->get($sub_p->product_id);
                if ($master_p == null) {
                    $products->push($sub_p);
                }
                else {
                    $master_p->quantity += $sub_p->quantity;
                    $master_p->delivered += $sub_p->delivered;

                    if($master_p->product->variants->isEmpty() == false) {
                        foreach($sub_p->variants as $sub_variant) {
                            $counter = 0;

                            foreach($master_p->variants as $master_variant) {
                                $counter = 0;

                                foreach($sub_variant->components as $sub_component) {
                                    $counter++;

                                    foreach($master_variant->components as $master_component) {
                                        if($master_component->variant_id == $sub_component->variant_id && $master_component->value_id == $sub_component->value_id) {
                                            $counter--;
                                            break;
                                        }
                                    }
                                }

                                if ($counter == 0)
                                    break;
                            }

                            if ($counter == 0) {
                                $master_variant->quantity += $sub_variant->quantity;
                                $master_variant->delivered += $sub_variant->delivered;
                            }
                            else {
                                $master_p->variants->push($sub_variant);
                            }
                        }
                    }
                }
            }
        }

        $products = $products->sort(function($a, $b) {
            return $a->product->name <=> $b->product->name;
        });

        return $products;
    }

    public function getFriendsBookingsAttribute()
    {
        return $this->innerCache('friends_bookings', function($obj) {
            $bookings = Booking::where('order_id', $obj->order_id)->whereIn('user_id', $obj->user->friends()->withTrashed()->pluck('id'))->get();

            foreach($bookings as $b) {
                $b->setRelation('order', $obj->order);
            }

            return $bookings;
        });
    }

    public function getValueWithFriendsAttribute()
    {
        $ret = $this->value;

        foreach($this->friends_bookings as $sub)
            $ret += $sub->value;

        return $ret;
    }

    public function getDeliveredWithFriendsAttribute()
    {
        $ret = $this->delivered;

        foreach($this->friends_bookings as $sub)
            $ret += $sub->delivered;

        return $ret;
    }

    public function getTotalFriendsValueAttribute()
    {
        $ret = 0;

        foreach($this->friends_bookings as $sub)
            $ret += $sub->total_value;

        return $ret;
    }

    public function getSlugID()
    {
        return sprintf('%s::%s', $this->order->id, $this->user->id);
    }

    public function printableName()
    {
        return $this->order->printableName();
    }

    public function getShowURL()
    {
        return URL::action('BookingUserController@show', $this->order->aggregate_id, $this->user_id);
    }

    /******************************************************** CreditableTrait */

    public function alterBalance($amount, $type = 'bank')
    {
        $this->order->supplier->alterBalance($amount, $type);
    }

    public static function balanceFields()
    {
        return [
            'bank' => _i('Saldo'),
        ];
    }
}
