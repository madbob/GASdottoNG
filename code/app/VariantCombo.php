<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VariantCombo extends Model
{
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
}
