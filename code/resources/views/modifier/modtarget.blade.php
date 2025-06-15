<x-larastrap::radios name="applies_target" tlabel="movements.reference_for_modifier" :options="$applies_targets" />

<div class="distribution_type_selection {{ $modifier->applies_target != 'order' || $modifier->value == 'price' ? 'd-none' : '' }}">
    <x-larastrap::radios name="distribution_type" tlabel="movements.distribute_on" :options="[
        'none' => (object) ['label' => __('texts.generic.none'), 'hidden' => true],
        'quantity' => __('texts.generic.quantity'),
        'price' => __('texts.generic.value'),
        'weight' => __('texts.generic.weight')
    ]" />
</div>
