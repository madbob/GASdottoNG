<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

class BookedProductVariant extends Model
{
    use GASModel, ReducibleTrait, Cachable;

    public function product()
    {
        return $this->belongsTo('App\BookedProduct', 'product_id');
    }

    public function components()
    {
        return $this->hasMany('App\BookedProductComponent', 'productvariant_id');
    }

    public function getStatusAttribute()
    {
        return $this->product->status;
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

    private function variantsCombo()
    {
        $values = [];

        foreach ($this->components as $c) {
            $values[] = $c->value_id;
        }

        return VariantCombo::byValues($values);
    }

    public function unitPrice($rectify = true)
    {
        $base_price = $this->product->basePrice($rectify);

        $combo = $this->variantsCombo();
        if ($combo) {
            $base_price += $combo->price_offset;
        }

        return $base_price;
    }

    public function fixWeight($attribute)
    {
        $weight = $this->product->product->weight;

        $combo = $this->variantsCombo();
        if ($combo) {
            $weight += $combo->weight_offset;
        }

        return $weight * $this->$attribute;
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

    public function getSupplierCodeAttribute()
    {
        $combo = $this->variantsCombo();
        if ($combo) {
            return $combo->code;
        }
        else {
            return '';
        }
    }

    /********************************************************* ReducibleTrait */

    protected function reduxBehaviour()
    {
        /*
            Essendo la variante prenotata la foglia più estrema dell'albero di
            riduzione, questa funzione non dovrebbe mai essere chiamata
        */
        throw new \Exception("Invocata funzione reduxBehaviour() su BookedProductVariant", 1);
    }

    public function reduxData($ret = null, $filters = null)
    {
        if (is_null($ret)) {
            $ret = (object) [
                'id' => $this->printableName(),
                'variant' => $this,

                'relative_price' => 0,
                'relative_weight' => 0,
                'relative_quantity' => 0,
                'relative_pieces' => 0,
            ];
        }

        $ret = $this->describingAttributesMerge($ret, (object) [
            'price' => $this->quantityValue(),
            'weight' => $this->fixWeight('quantity'),

            /*
                Cfr. comportamento di BookedProduct
            */
            'quantity' => $this->quantity,
            'quantity_pieces' => $this->quantity,

            'price_delivered' => $this->deliveredValue(),
            'weight_delivered' => $this->fixWeight('delivered'),
            'delivered' => $this->delivered,
            'delivered_pieces' => $this->product->product->portion_quantity > 0 ? $this->delivered * $this->product->product->portion_quantity : $this->delivered,
        ]);

        $status = $this->status;

        if ($status == 'shipped' || $status == 'saved') {
            $ret->relative_price += $ret->price_delivered;
            $ret->relative_weight += $ret->weight_delivered;
            $ret->relative_quantity += $ret->delivered;
            $ret->relative_pieces += $ret->delivered_pieces;
        }
        else {
            $ret->relative_price += $ret->price;
            $ret->relative_weight += $ret->weight;
            $ret->relative_quantity += $ret->quantity;
            $ret->relative_pieces += $ret->quantity_pieces;
        }
    }
}
