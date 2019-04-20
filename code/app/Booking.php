<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

use Auth;
use DB;
use URL;
use Log;

use App\Events\SluggableCreating;
use App\Events\BookingDeleting;
use App\GASModel;
use App\CreditableTrait;
use App\SluggableID;
use App\BookedProduct;

class Booking extends Model
{
    use GASModel, SluggableID, PayableTrait, CreditableTrait;

    public $incrementing = false;

    protected $dispatchesEvents = [
        'creating' => SluggableCreating::class,
        'deleting' => BookingDeleting::class,
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('gas', function (Builder $builder) {
            $builder->whereHas('user', function($query) {
                $user = Auth::user();
                if (is_null($user))
                    return;
                $query->where('gas_id', $user->gas->id);
            });
        });
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
        return $this->innerCache('value', function($obj) {
            $value = 0;

            foreach ($obj->products as $booked) {
                $booked->setRelation('booking', $this);
                $value += $booked->quantityValue();
            }

            return $value;
        });
    }

    /*
        Valore complessivo di quanto consegnato.
        Se la prenotazione è stata effettivamente consegnata somma i prezzi
        finali salvati sul database, altrimenti (e.g. la prenotazione è stata
        salvata, ma non ancora consegnata) li ricalcola usando la quantità
        consegnata come riferimento.
    */
    public function getDeliveredAttribute()
    {
        return $this->innerCache('delivered', function($obj) {
            if ($obj->status == 'shipped') {
                return $obj->products()->sum('final_price');
            }
            else {
                $value = 0;

                foreach ($obj->products as $booked) {
                    $booked->setRelation('booking', $this);
                    $value += $booked->deliveredValue();
                }

                return $value;
            }
        });
    }

    /*
        Trasporto complessivo di quanto consegnato
    */
    public function getTransportedAttribute()
    {
        return $this->products()->sum('final_transport');
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
        Questo ritorna solo il costo di trasporto applicato sull'ordine
        complessivo
    */
    public function getMajorTransportAttribute()
    {
        return $this->innerCache('major_transport', function($obj) {
            if($obj->order->transport > 0) {
                $total_value = $obj->order->total_value;
                if ($total_value != 0) {
                    if (is_numeric($obj->order->transport)) {
                        return round($obj->value_with_friends * $obj->order->transport / $total_value, 2);
                    }
                    else {
                        return $obj->value_with_friends - applyPercentage($obj->value_with_friends, $obj->order->transport);
                    }
                }
            }

            return 0;
        });
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
        return $this->innerCache('transport', function($obj) {
            $transport = $obj->major_transport;

            if ($obj->status == 'shipped') {
                $transport += $obj->products()->sum('final_transport');
            }
            else {
                foreach($obj->products as $p) {
                    $transport += $p->transportDeliveredValue();
                }
            }

            return $transport;
        });
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
        $this->load('products');

        $global_transport = $this->major_transport;
        $booking_value = $this->delivered;
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

    private function getDiscount($value_field)
    {
        if (!empty($this->order->discount) && $this->order->discount != 0) {
            if (is_numeric($this->order->discount)) {
                $total_value = $this->order->total_value;
                if ($total_value != 0) {
                    return round($this->$value_field * $this->order->discount / $total_value, 2);
                }
            }
            else {
                return applyPercentage($this->$value_field, $this->order->discount, '=');
            }
        }

        return 0;
    }

    public function getMajorDiscountWithFriendsAttribute()
    {
        return $this->getDiscount('value_with_friends');
    }

    public function getMajorDiscountAttribute()
    {
        if ($obj->status == 'shipped') {
            return $obj->products()->sum('final_discount');
        }
        else {
            return $this->getDiscount('value');
        }
    }

    /*
        Questa funzione serve a spalmare lo sconto globale applicato all'ordine
        (e, dunque, alla prenotazione) su tutti i prodotti coinvolti, o in modo
        proporzionale (se lo sconto è assoluto) o in modo percentuale.
        Da applicare solo dopo aver fissato il final_price dei prodotti
        consegnati
    */
    public function distributeDiscount()
    {
        if(!empty($this->order->discount) && $this->order->discount != 0) {
            $this->load('products');

            if (is_numeric($this->order->discount)) {
                $total_value = $this->order->total_value;
                if ($total_value != 0) {
                    $distributed_amount = 0;
                    $global_discount = $this->major_discount;
                    $global_value = $this->value;

                    foreach($this->products as $p) {
                        $p->final_discount = round($p->final_price * $global_discount / $global_value, 3);
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
        return $this->delivered + $this->check_transport;
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

    public function getValueWithFriendsAttribute()
    {
        return $this->innerCache('value_with_friends', function($obj) {
            $ret = $obj->value;

            foreach($obj->friends_bookings as $sub)
                $ret += $sub->value;

            return $ret;
        });
    }

    public function getTotalValueWithFriendsAttribute()
    {
        return $this->value_with_friends + $this->check_transport - $this->major_discount_with_friends;
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
                        return $a_place <=> $b_place;
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

        $tot = $this->total_value;
        $friends_tot = $this->total_friends_value;

        if($tot == 0 && $friends_tot == 0) {
            $message = _i("Non hai partecipato a quest'ordine");
        }
        else {
            if ($friends_tot == 0)
                $message = _i('Hai ordinato %s', printablePriceCurrency($tot));
            else
                $message = _i('Hai ordinato %s + %s', printablePriceCurrency($tot), printablePriceCurrency($friends_tot));
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
