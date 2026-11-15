<?php

namespace App\Parameters\ModifierTarget;

use Illuminate\Database\Eloquent\Model;

use App\ModifierType;

class Aggregate extends ModifierTarget
{
    public function modelType(): string
    {
        return 'aggregate';
    }

    public function initNewModifier(ModifierType $modtype, Model $reference)
    {
        $mod = parent::initNewModifier($modtype, $reference);
        $mod->applies_target = 'booking_aggregate';
        $mod->save();
        return $mod;
    }

    public function configParams()
    {
        return [
            'always_on' => false,

            'applies_types' => [
                'none' => __('texts.movements.modifier_no_theshold'),
                'price' => __('texts.generic.value'),
                'weight' => __('texts.generic.weight'),
            ],

            'values' => [
                'absolute' => __('texts.generic.absolute'),
                'percentage' => __('texts.generic.percentage'),
                'mass' => __('texts.generic.by_weight'),
            ],

            'applies_targets' => [
                'booking_aggregate' => __('texts.movements.apply_to_aggregate_booking'),
                'order_aggregate' => __('texts.movements.apply_to_aggregate_order'),
            ],
        ];
    }
}
