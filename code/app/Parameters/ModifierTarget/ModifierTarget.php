<?php

namespace App\Parameters\ModifierTarget;

use Illuminate\Database\Eloquent\Model;

use App\Modifier;
use App\ModifierType;

abstract class ModifierTarget
{
    abstract public function modelType(): string;

    public function initNewModifier(ModifierType $modtype, Model $reference)
    {
        $mod = new Modifier();
        $mod->modifier_type_id = $modtype->id;
        $mod->target_type = get_class($reference);
        $mod->target_id = (string) $reference->id;
        $mod->applies_type = 'none';
        $mod->applies_target = 'booking';
        $mod->distribution_type = 'price';
        $mod->definition = '[]';
        $mod->save();
        return $mod;
    }

    public function configParams()
    {
        return [
            'always_on' => false,

            // Misura su cui applicare le soglie
            'applies_types' => [
                'none' => __('texts.movements.modifier_no_theshold'),
                'price' => __('texts.generic.value'),
                'weight' => __('texts.generic.weight'),
            ],

            // Valore
            'values' => [
                'absolute' => __('texts.generic.absolute'),
                'percentage' => __('texts.generic.percentage'),
                'mass' => __('texts.generic.by_weight'),
            ],

            // Riferimento su cui applicare il modificatore
            'applies_targets' => [
                'booking' => __('texts.movements.apply_to_booking'),
                'order' => __('texts.movements.apply_to_order'),
            ],
        ];
    }

    public function movementTypes()
    {
        $types = [ 'none' => __('texts.generic.none') ];
        $booking_payment_type = movementTypes('booking-payment');
        $movement_type_alert = null;

        foreach (movementTypes() as $info) {
            if ($info->overlapsPaymentMethods($booking_payment_type) == false) {
                $movement_type_alert = __('texts.movements.help.missing_movements_for_modifiers', ['methods' => join(', ', paymentsByType('booking-payment'))]);
            }
            else if ($info->visibility) {
                $types[$info->id] = $info->name;
            }
        }

        return [$types, $movement_type_alert];
    }
}
