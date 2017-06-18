<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\GASModel;
use App\SluggableID;
use App\BookedProduct;

class Booking extends Model
{
    use GASModel, SluggableID, PayableTrait;

    public $incrementing = false;

    public function user()
    {
        return $this->belongsTo('App\User');
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

    public function getBooked($product, $fallback = false)
    {
        $p = $this->products()->whereHas('product', function ($query) use ($product) {
            $query->where('id', '=', $product->id);
        })->first();

        if ($p == null && $fallback == true) {
            $p = new BookedProduct();
            $p->booking_id = $this->id;
            $p->product_id = $product->id;
        }

        return $p;
    }

    public function getBookedQuantity($product)
    {
        $p = $this->getBooked($product);

        if ($p == null) {
            return 0;
        } else {
            return $p->quantity;
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
            $sum += $booked->deliveredValue();
        }

        return $sum;
    }

    public function getTransportAttribute()
    {
        if($this->order->transport)
            return ($this->order->transport / $this->order->bookings()->count());
        else
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
}
