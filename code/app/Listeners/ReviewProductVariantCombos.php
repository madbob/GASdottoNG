<?php

namespace App\Listeners;

use App\Events\VariantChanged;
use App\VariantCombo;

class ReviewProductVariantCombos
{
    public function __construct()
    {
        //
    }

    public function handle(VariantChanged $event)
    {
        $product = $event->variant->product;

        /*
            Per sicurezza qui viene forzato una nuova lettura delle varianti
            coinvolte
        */
        $product->unsetRelation('variants');

        $combos = [[]];
        $values = [];
        $all_values = [];

        foreach($product->variants as $variant) {
            $variant_values = [];

            foreach($variant->values as $value) {
                $variant_values[] = $value->id;
                $all_values[] = $value->id;
            }

            $values[] = $variant_values;
        }

        $length = count($values);

        for ($count = 0; $count < $length; $count++) {
            $tmp = [];

            foreach ($combos as $v1) {
                foreach ($values[$count] as $v2) {
                    $tmp[] = array_merge($v1, [$v2]);
                }
            }

            $combos = $tmp;
        }

        $valid_ids = [];

        foreach($combos as $combo) {
            $vc = VariantCombo::byValues($combo);
            if (is_null($vc)) {
                $vc = new VariantCombo();
                $vc->save();
                $vc->values()->sync($combo);
            }

            $valid_ids[] = $vc->id;
        }

        VariantCombo::whereHas('values', function($query) use ($all_values) {
            $query->whereIn('variant_value_id', $all_values);
        })->whereNotIn('id', $valid_ids)->delete();
    }
}
