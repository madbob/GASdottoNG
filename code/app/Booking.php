<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

use Auth;
use DB;
use URL;
use Log;

use App\Scopes\RestrictedGAS;
use App\Events\SluggableCreating;
use App\Events\BookingDeleting;

class Booking extends Model
{
    use GASModel, SluggableID, PayableTrait, CreditableTrait;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $dispatchesEvents = [
        'creating' => SluggableCreating::class,
        'deleting' => BookingDeleting::class,
    ];

    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope(new RestrictedGAS('user'));
    }

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
        return $this->belongsTo('App\Order')->withoutGlobalScopes();
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

    public function dynamicTransportCost($with_friends, $including_products_transport = true)
    {
        $value = 0;

        if($this->order->transport > 0) {
            if ($this->status == 'shipped' || $this->status == 'saved') {
                $booking_value = $this->getValue('delivered', $with_friends);
            }
            else {
                $booking_value = $this->getValue('booked', $with_friends);
            }

            if (is_numeric($this->order->transport)) {
                $total_value = $this->order->total_value;
                if ($total_value != 0) {
                    $value = round($booking_value * $this->order->transport / $total_value, 2);
                }
            }
            else {
                $value = applyPercentage($booking_value, $this->order->transport, '=');
            }
        }

        if ($including_products_transport) {
            if ($with_friends) {
                $transported = $this->products_with_friends->filter(function($item) {
                    return $item->product->transport != 0;
                });
            }
            else {
                $transported = $this->products()->whereHas('product', function($query) {
                    $query->where('transport', '!=', 0);
                })->with('product')->get();
            }

            foreach($transported as $t) {
                if (is_numeric($t->product->transport)) {
                    $value += $t->quantity * $t->product->transport;
                }
                else {
                    $value += applyPercentage($t->quantityValue(), $t->product->transport, '=');
                }
            }
        }

        return $value;
    }

    private function dynamicDiscount($with_friends)
    {
        $value = 0;

        if (!empty($this->order->discount) && $this->order->discount != 0) {
            if ($this->status == 'shipped' || $this->status == 'saved') {
                $booking_value = $this->getValue('delivered', $with_friends);
            }
            else {
                $booking_value = $this->getValue('booked', $with_friends);
            }

            if (is_numeric($this->order->discount)) {
                $total_value = $this->order->total_value;
                if ($total_value != 0) {
                    $value = round($booking_value * $this->order->discount / $total_value, 2);
                }
            }
            else {
                $value = applyPercentage($booking_value, $this->order->discount, '=');
            }
        }

        $discounted = $this->products()->whereHas('product', function($query) {
            $query->where('discount', '!=', 0);
        })->with('product')->get();

        foreach($discounted as $d) {
            if (is_numeric($d->product->discount)) {
                $value += $d->quantity * $d->product->discount;
            }
            else {
                $value += applyPercentage($d->quantityValue(), $d->product->discount, '=');
            }
        }

        return $value;
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
            $this->unsetRelation('products');
        }

        return $this->innerCache($key, function($obj) use ($type, $with_friends) {
            $value = 0;

            /*
                Il totale di quanto prenotato non cambia a seconda che la
                prenotazione sia consegnata o meno
            */
            if ($type == 'booked') {
                if ($with_friends) {
                    $products = $obj->products_with_friends;
                }
                else {
                    $products = $obj->products;
                }

                foreach ($products as $booked) {
                    $booked->setRelation('booking', $obj);
                    $value += $booked->quantityValue();
                }
            }
            else {
                if ($obj->status == 'shipped' || $obj->status == 'saved') {
                    switch($type) {
                        case 'effective':
                            return $obj->getValue('delivered', $with_friends) + $obj->getValue('transport', $with_friends) - $obj->getValue('discount', $with_friends);

                        case 'delivered':
                            $value = $obj->products()->sum('final_price');
                            break;

                        case 'transport':
                            $value = $obj->products()->sum('final_transport');
                            break;

                        case 'discount':
                            $value = $obj->products()->sum('final_discount');
                            break;
                    }

                    if ($with_friends) {
                        foreach($obj->friends_bookings as $sub) {
                            $value += $sub->getValue($type, $with_friends);
                        }
                    }
                }
                else {
                    switch($type) {
                        case 'effective':
                            return $obj->getValue('booked', $with_friends) + $obj->getValue('transport', $with_friends) - $obj->getValue('discount', $with_friends);

                        /*
                            Se la prenotazione non è consegnata, implicitamente il
                            valore di quanto consegnato è 0
                        */
                        case 'delivered':
                            $value = 0;
                            break;

                        case 'transport':
                            $value = $obj->dynamicTransportCost($with_friends);
                            break;

                        case 'discount':
                            $value = $obj->dynamicDiscount($with_friends);
                            break;
                    }
                }
            }

            return $value;
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

        if (is_null($p) && $fallback == true) {
            $p = new BookedProduct();
            $p->booking_id = $this->id;
            $p->product_id = $product_id;
        }

        return $p;
    }

    private function readProductQuantity($product, $field, $friends_bookings)
    {
        $p = $this->getBooked($product);

        if (is_null($p))
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

    /*
        Questa funzione serve a spalmare il costo di trasporto globale applicato
        all'ordine (e, dunque, alla prenotazione) su tutti i prodotti coinvolti,
        in modo proporzionale.
        Da applicare solo dopo aver fissato il final_price dei prodotti
        consegnati
    */
    public function distributeTransport()
    {
        if(!empty($this->order->transport) && $this->order->transport != 0) {
            $this->load('products');

            $global_transport = $this->dynamicTransportCost(false);
            $booking_value = $this->getValue('delivered', false);
            $distributed_amount = 0;
            $last_product = null;

            foreach($this->products as $p) {
                if ($booking_value != 0)
                    $per_product = round(($global_transport * $p->final_price) / $booking_value, 2);
                else
                    $per_product = 0;

                $p->final_transport = $p->transportDeliveredValue() + $per_product;
                $p->save();
                $distributed_amount += $per_product;
                $last_product = $p;
            }

            if ($distributed_amount != $global_transport && $last_product != null) {
                Log::debug('Arrotondo prezzo di trasporto su ultimo prodotto, ' . ($global_transport - $distributed_amount));
                $last_product->final_transport += ($global_transport - $distributed_amount);
                $last_product->save();
            }
        }
    }

    /*
        Questa funzione serve a spalmare lo sconto globale applicato all'ordine
        (e, dunque, alla prenotazione) su tutti i prodotti coinvolti, o in modo
        proporzionale (se lo sconto è assoluto) o in modo percentuale.
        Da applicare solo dopo aver fissato il final_price dei prodotti
        consegnati e lo stato della prenotazione, in quanto varia il
        comportamento di dynamicDiscount()
    */
    public function distributeDiscount()
    {
        if(!empty($this->order->discount) && $this->order->discount != 0) {
            $this->load('products');

            if (is_numeric($this->order->discount)) {
                $total_value = $this->order->total_value;
                if ($total_value != 0) {
                    $distributed_amount = 0;
                    $last_product = null;
                    $global_discount = $this->dynamicDiscount(false);
                    $booking_value = $this->getValue('delivered', false);

                    foreach($this->products as $p) {
                        if ($booking_value != 0)
                            $per_product = round($p->final_price * $global_discount / $booking_value, 3);
                        else
                            $per_product = 0;

                        $p->final_discount = $p->discountDeliveredValue() + $per_product;
                        $p->save();
                        $distributed_amount += $p->final_discount;
                        $last_product = $p;
                    }

                    if ($distributed_amount != $global_discount && $last_product != null) {
                        Log::debug('Arrotondo sconto su ultimo prodotto, ' . ($global_discount - $distributed_amount));
                        $last_product->final_discount += ($global_discount - $distributed_amount);
                        $last_product->save();
                    }
                }
            }
            else {
                foreach($this->products as $p) {
                    $p->final_discount = applyPercentage($p->final_price, $this->order->discount, '=');
                    $p->save();
                }
            }
        }
        else {
            /*
                Questo è per annullare eventuali sconti che sono stati nel
                frattempo rimossi dall'ordine
            */
            foreach($this->products as $p) {
                if ($p->final_discount != 0) {
                    $p->final_discount = 0;
                    $p->save();
                }
            }
        }
    }

    public function getProductsWithFriendsAttribute()
    {
        return $this->innerCache('friends_products', function($obj) {
            $products = $this->products;
            $friends = $this->friends_bookings;

            foreach($friends as $sub) {
                foreach($sub->products as $sub_p) {
                    $master_p = $products->keyBy('product_id')->get($sub_p->product_id);
                    if (is_null($master_p)) {
                        $products->push($sub_p);
                    }
                    else {
                        $master_p->quantity += $sub_p->quantity;
                        $master_p->delivered += $sub_p->delivered;
                        $master_p->final_price += $sub_p->final_price;
                        $master_p->final_transport += $sub_p->final_transport;
                        $master_p->final_discount += $sub_p->final_discount;

                        if($master_p->product->variants->isEmpty() == false) {
                            foreach($sub_p->variants as $sub_variant) {
                                $master_p->variants->push($sub_variant);
                            }
                        }
                    }
                }
            }

            $products = $products->sort(function($a, $b) {
                return $a->product->name <=> $b->product->name;
            });

            return $products;
        });
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

    public function getTotalFriendsValueAttribute()
    {
        $ret = 0;

        foreach($this->friends_bookings as $sub)
            $ret += $sub->getValue('effective', false);

        return $ret;
    }

    public static function sortByShippingPlace($bookings, $shipping_place)
    {
        if ($shipping_place == 'all_by_name') {
            usort($bookings, function($a, $b) {
                return $a->user->printableName() <=> $b->user->printableName();
            });
        }
        else if ($shipping_place == 'all_by_place') {
            usort($bookings, function($a, $b) {
                $a_place = $a->user->shippingplace;
                $b_place = $b->user->shippingplace;

                if (is_null($a_place) && is_null($b_place)) {
                    return $a->user->printableName() <=> $b->user->printableName();
                }
                else if (is_null($a_place)) {
                    return -1;
                }
                else if (is_null($b_place)) {
                    return 1;
                }
                else {
                    if ($a_place->id != $b_place->id)
                        return $a_place->name <=> $b_place->name;
                    else
                        return $a->user->printableName() <=> $b->user->printableName();
                }
            });
        }
        else {
            $tmp_bookings = [];

            foreach($bookings as $booking)
                if ($booking->user->preferred_delivery_id == $shipping_place)
                    $tmp_bookings[] = $booking;

            $bookings = $tmp_bookings;

            usort($bookings, function($a, $b) {
                return $a->user->printableName() <=> $b->user->printableName();
            });
        }

        return $bookings;
    }

    public function printableName()
    {
        return $this->order->printableName();
    }

    public function printableHeader()
    {
        $ret = $this->printableName();

        $user = Auth::user();

        $tot = $this->getValue('effective', false);
        $friends_tot = $this->total_friends_value;

        if($tot == 0 && $friends_tot == 0) {
            $message = _i("Non hai partecipato a quest'ordine");
        }
        else {
            if ($friends_tot == 0)
                $message = _i('Hai ordinato %s', printablePriceCurrency($tot));
            else
                $message = _i('Hai ordinato %s + %s', [printablePriceCurrency($tot), printablePriceCurrency($friends_tot)]);
        }

        $ret .= '<span class="pull-right">' . $message . '</span>';
        return $ret;
    }

    public function getShowURL()
    {
        return route('booking.user.show', ['booking' => $this->order->aggregate_id, 'user' => $this->user_id]);
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

    public static function balanceFields()
    {
        return [
            'bank' => _i('Saldo'),
        ];
    }
}
