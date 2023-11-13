<?php

/*
    Questa classe rappresenta ciascuna combinazione di varianti assegnabili ad
    un prodotto, coi suoi propri attributi.
    Reminder: è sconsigliato gestire qui il prezzo della combinazione (somma tra
    il prezzo base del prodotto e l'eventuale differenza prezzo), in quanto
    vanno tenuti in considerazione anche i modificatori che si applicano su ogni
    specifico ordine
*/

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

use App\Models\Concerns\Priceable;

class VariantCombo extends Model
{
    use Priceable, Cachable;

    public function values(): BelongsToMany
    {
        return $this->belongsToMany(VariantValue::class, 'variant_combo_values');
    }

    public function getProductAttribute()
    {
        return $this->values->first()->variant->product;
    }

    public function getPriceAttribute()
    {
        return $this->getPrice();
    }

    public function printableShortName()
    {
        $ret = [];

        foreach ($this->values as $val) {
            $ret[] = $val->value;
        }

        return implode(', ', $ret);
    }

    public function printableName()
    {
        return sprintf('%s - %s', $this->product->printableName(), $this->printableShortName());
    }

    public static function byValues($values)
    {
        $query = self::orderBy('id', 'asc');

        foreach($values as $value) {
            $query->whereHas('values', function($query) use ($value) {
                $query->where('variant_value_id', $value);
            });
        }

        return $query->first();
    }

    public function innerIdentifier()
    {
        return $this->values->sortBy('id')->pluck('id')->join(' ');
    }

    public static function activeValues($combos)
    {
        $ret = [];

        $combos = $combos->filter(function($combo) {
            return $combo->active;
        });

        foreach($combos as $combo) {
            foreach($combo->values->sortBy('value') as $value) {
                $variant_id = $value->variant_id;
                if (!isset($ret[$variant_id])) {
                    $ret[$variant_id] = [];
                }

                $ret[$variant_id][$value->id] = $value->value;
            }
        }

        foreach($ret as $variant_id => $values) {
            asort($values);
            $ret[$variant_id] = $values;
        }

        return $ret;
    }

    /************************************************************** Priceable */

    public function realPrice($rectify)
    {
        $offset = $this->price_offset;
        $product = $this->product;

        if (isset($product->pivot->prices)) {
            $prices = json_decode($product->pivot->prices);
            if ($prices) {
                $key = $this->innerIdentifier();
                if (isset($prices->variants->$key)) {
                    $offset = $prices->variants->$key;
                }
            }
        }

        return $offset + $product->getPrice($rectify);
    }
}
