<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\GASModel;

class BookedProductVariant extends Model
{
    use GASModel;

    public function product()
    {
        return $this->belongsTo('App\BookedProduct', 'product_id');
    }

    public function components()
    {
        return $this->hasMany('App\BookedProductComponent', 'productvariant_id');
    }

    public function hasCombination($variant, $value)
    {
        $components = $this->components;

        foreach ($components as $c) {
            if ($c->variant_id == $variant->id && $c->value_id == $value->id) {
                return true;
            }
        }

        return false;
    }

    public function unitPrice($rectify = true)
    {
        $base_price = $this->product->basePrice($rectify);
        $price = $base_price;

        foreach ($this->components as $c) {
            $price += $c->value->price_offset;
        }

        return $price;
    }

    private function fixQuantity($attribute, $rectify)
    {
        return $this->unitPrice($rectify) * $this->$attribute;
    }

    public function quantityValue()
    {
        return $this->fixQuantity('quantity', true);
    }

    public function deliveredValue()
    {
        return $this->fixQuantity('delivered', false);
    }

    public function printableName()
    {
        $ret = [];
        $components = $this->components;

        foreach ($components as $c) {
            $ret[] = $c->value->value;
        }

        return implode(', ', $ret);
    }

    private function normalizeQuantity($attribute)
    {
        $product = $this->product->product;
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
