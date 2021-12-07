<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

class VariantCombo extends Model
{
    use Cachable;

    public function values()
    {
        return $this->belongsToMany('App\VariantValue', 'variant_combo_values');
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

        foreach($combos as $combo) {
            if ($combo->active == false) {
                continue;
            }

            foreach($combo->values()->orderBy('value', 'asc')->get() as $value) {
                $variant_id = $value->variant_id;
                if (!isset($ret[$variant_id])) {
                    $ret[$variant_id] = [];
                }

                $ret[$variant_id][$value->id] = $value->value;
            }
        }

        return $ret;
    }
}
