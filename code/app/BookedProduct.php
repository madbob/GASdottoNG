<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\GASModel;
use App\SluggableID;

class BookedProduct extends Model
{
    use GASModel, SluggableID;

    public $incrementing = false;

    public function product()
    {
        return $this->belongsTo('App\Product');
    }

    public function booking()
    {
        return $this->belongsTo('App\Booking');
    }

    public function variants()
    {
        return $this->hasMany('App\BookedProductVariant', 'product_id');
    }

    public function getSlugID()
    {
        return sprintf('%s::%s', $this->booking->id, $this->product->id);
    }

    private function fixQuantity($attribute)
    {
        /*
            Per i prodotti con pezzatura, basePrice() già fornisce il prezzo per
            singola unità. Non è dunque qui necessario effettuare altri
            controlli o aggiustamenti
        */
        $base_price = $this->basePrice();
        $product = $this->product;

        $variants = $this->variants;
        if ($variants->isEmpty() == false) {
            $total = 0;

            foreach ($variants as $v) {
                $price = $base_price;

                foreach ($v->components as $c) {
                    $price += $c->value->price_offset;
                }

                $total += ($price + $product->transport) * $v->$attribute;
            }

            return $total;
        }
        else {
            return ($base_price + $product->transport) * $this->$attribute;
        }
    }

    public function basePrice()
    {
        $product = $this->product;
        return $product->contextualPrice($this->booking->order) + $product->transport;
    }

    public function quantityValue()
    {
        return $this->fixQuantity('quantity');
    }

    public function deliveredValue()
    {
        return $this->fixQuantity('delivered');
    }

    public function getBookedVariant($variant, $fallback = false)
    {
        $v = $this->variants()->where('id', '=', $variant->id)->first();

        if ($v == null && $fallback == true) {
            $v = new BookedProductVariant();
            $v->product_id = $this->id;
        }

        return $v;
    }

    private function normalizeQuantity($attribute)
    {
        $product = $this->product;
        if ($product->portion_quantity != 0)
            return $this->$attribute * $product->portion_quantity;
        else
            return $this->$attribute;
    }

    public function getTrueQuantityAttribute()
    {
        return $this->normalizeQuantity('quantity');
    }

    public function getTrueDeliveredAttribute()
    {
        return $this->normalizeQuantity('delivered');
    }
}
