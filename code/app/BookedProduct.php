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
        $product = $this->product;
        $base_price = $product->contextualPrice($this->booking->order) + $product->transport;

        $variants = $this->variants;
        if ($variants->isEmpty() == false) {
            $total = 0;

            foreach ($variants as $v) {
                $price = $base_price;

                foreach ($v->components as $c) {
                    $price += $c->value->price_offset;
                }

                $total += $price * $v->$attribute;
            }

            return $total;
        } else {
            $quantity = $this->$attribute;
            if ($product->portion_quantity != 0) {
                $quantity = $this->$attribute * $product->portion_quantity;
            }

            return ($base_price + $product->transport) * $quantity;
        }
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
}
