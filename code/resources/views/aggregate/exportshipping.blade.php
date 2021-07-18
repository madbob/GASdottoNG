<?php

$hub = App::make('GlobalScopeHub');

if ($hub->enabled() == false) {
    $active_gas = null;
    $managed_gas = 0;
}
else {
    $active_gas = $hub->getGasObj();
    $managed_gas = $active_gas->id;
}

?>

<x-larastrap::modal :title="_i('Dettaglio Consegne Aggregato')">
    <x-larastrap::form classes="direct-submit" method="GET" :action="route('aggregates.document', ['id' => $aggregate->id, 'type' => 'shipping'])">
        <p>
            {{ _i("Da qui puoi ottenere un documento PDF formattato per la stampa, in cui si trovano le informazioni relative alle singole prenotazioni di tutti gli ordini inclusi in questo aggregato.") }}
        </p>

        <hr>

        <input type="hidden" name="managed_gas" value="{{ $managed_gas }}">

        @if($currentgas->hasFeature('shipping_places'))
            <?php

            $options = [
                'all_by_name' => _i('Tutti (ordinati per utente)'),
                'all_by_place' => _i('Tutti (ordinati per luogo)'),
            ];

            foreach($currentgas->deliveries as $delivery) {
                $options[$delivery->id] = $delivery->name;
            }

            ?>
            <x-larastrap::radios name="shipping_place" :label="_i('Luogo di Consegna')" :options="$options" value="all_by_name" />
        @endif

        <?php list($options, $values) = flaxComplexOptions(App\User::formattableColumns()) ?>
        <x-larastrap::checks name="fields" :label="_i('Dati Utenti')" :options="$options" :value="$values" />

        <?php list($options, $values) = flaxComplexOptions(App\Order::formattableColumns('shipping')) ?>
        <x-larastrap::checks name="fields" :label="_i('Colonne Prodotti')" :options="$options" :value="$values" />

        <x-larastrap::radios name="format" :label="_i('Formato')" :options="['pdf' => _i('PDF'), 'csv' => _i('CSV')]" value="pdf" />
    </x-larastrap::form>
</x-larastrap::modal>
