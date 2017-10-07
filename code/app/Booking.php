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
        });
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

        if ($p == null && $fallback == true) {
            $p = new BookedProduct();
            $p->booking_id = $this->id;
            $p->product_id = $product_id;
        }

        return $p;
    }

    public function getBookedQuantity($product, $real = false)
    {
        $p = $this->getBooked($product);

        if ($p == null) {
            return 0;
        }
        else {
            if ($real)
                return $p->true_quantity;
            else
                return $p->quantity;
        }
    }

    /*
        $real: in caso di prodotti con pezzatura, se == false restituisce la
        quantità eventualmente normalizzata in numeri di pezzi altrimenti
        restituisce la quantità intera.
        In caso di prodotti senza pezzatura, restituisce sempre la quantità
        consegnata non ulteriormente elaborata
    */
    public function getDeliveredQuantity($product, $real = false)
    {
        $p = $this->getBooked($product);

        if ($p == null) {
            return 0;
        }
        else {
            if ($real)
                return $p->true_delivered;
            else
                return $p->delivered;
        }
    }

    /*
        Valore complessivo di quanto ordinato
    */
    public function getValueAttribute()
    {
        $sum = 0;

        foreach ($this->products as $booked) {
            $sum += $booked->quantityValue();
        }

        return $sum;
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

    public function getTransportAttribute()
    {
        if($this->order->transport > 0) {
            /*
            $count = $this->order->bookings()->count();
            if ($count != 0)
                return ($this->order->transport / $count);
            */

            $total_value = $this->order->total_value;
            if ($total_value == 0)
                return 0;

            return round($this->value * $this->order->transport / $total_value, 2);
        }

        return 0;
    }

    /*
        Valore complessivo di quanto ordinato + spedizione globale
    */
    public function getTotalValueAttribute()
    {
        return $this->value + $this->transport;
    }

    /*
        Valore complessivo di quanto consegnato + spedizione globale
    */
    public function getTotalDeliveredAttribute()
    {
        return $this->delivered + $this->transport;
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
            'bank' => 'Saldo',
        ];
    }
}
