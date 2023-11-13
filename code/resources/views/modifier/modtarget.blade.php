<x-larastrap::radios name="applies_target" :label="_i('Riferimento su cui applicare il modificatore')" :options="$applies_targets" />

<div class="distribution_type_selection {{ $modifier->applies_target != 'order' || $modifier->value == 'price' ? 'd-none' : '' }}">
    <x-larastrap::radios name="distribution_type" :label="_i('Distribuzione sulle prenotazioni in base a')" :options="['none' => (object) ['label' => _i('Nessuno'), 'hidden' => true], 'quantity' => _i('Quantità'), 'price' => _i('Valore'), 'weight' => _i('Peso')]" />
</div>
