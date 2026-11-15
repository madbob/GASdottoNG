<?php

namespace App\Parameters\ModifierTarget;

use Illuminate\Database\Eloquent\Model;

use App\ModifierType;

class Product extends ModifierTarget
{
    public function modelType(): string
    {
        return 'product';
    }

    public function initNewModifier(ModifierType $modtype, Model $reference)
    {
        $mod = parent::initNewModifier($modtype, $reference);
        $mod->applies_target = 'product';
        $mod->save();
        return $mod;
    }

    public function configParams()
    {
        $ret = parent::configParams();

        $ret['applies_types'] = [
            'none' => __('texts.movements.modifier_no_theshold'),
            'quantity' => __('texts.generic.quantity'),
            'price' => __('texts.generic.value'),
            'order_price' => __('texts.movements.order_value'),
            'weight' => __('texts.generic.weight'),
        ];

        $ret['values'] = [
            'absolute' => __('texts.generic.absolute'),
            'percentage' => __('texts.generic.percentage'),
            'mass' => __('texts.generic.by_weight'),
            'price' => __('texts.products.prices.unit'),
        ];

        $ret['applies_targets'] = [
            'product' => __('texts.products.name'),
            'booking' => __('texts.movements.apply_to_booking'),
            'order' => __('texts.movements.apply_to_order'),
        ];

        return $ret;
    }
}
