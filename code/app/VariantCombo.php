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

class VariantCombo extends Model
{
    use Cachable;

    public function values(): BelongsToMany
    {
        return $this->belongsToMany('App\VariantValue', 'variant_combo_values');
    }

    public function getProductAttribute()
    {
        return $this->values->first()->variant->product;
    }

    public function getPriceAttribute()
    {
        return $this->price_offset + $this->product->price;
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

    public static function activeValues($combos)
    {
        $ret = [];

        $combos = $combos->filter(function($combo) {
            return $combo->active;
        });

        foreach($combos as $combo) {
            foreach($combo->values()->orderBy('value', 'asc')->get() as $value) {
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
}
