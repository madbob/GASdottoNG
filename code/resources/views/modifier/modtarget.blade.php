<x-larastrap::radios name="applies_target" tlabel="movements.reference_for_modifier" :options="$applies_targets" />

<div class="distribution_type_selection {{ $modifier->applies_target != 'order' || $modifier->value == 'price' ? 'd-none' : '' }}">
    <x-larastrap::radios name="distribution_type" tlabel="movements.distribute_on" :options="[
        'none' => (object) ['label' => __('generic.none'), 'hidden' => true],
        'quantity' => __('generic.quantity'),
        'price' => __('generic.value'),
        'weight' => __('generic.weight')
    ]" />
</div>
