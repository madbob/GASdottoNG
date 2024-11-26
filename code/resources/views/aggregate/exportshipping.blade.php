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

        @include('commons.selectshippingexport', ['aggregate' => $aggregate, 'included_metaplace' => ['all_by_name', 'all_by_place']])

        <?php list($options, $values) = flaxComplexOptions(App\Formatters\User::formattableColumns('shipping')) ?>
        <x-larastrap::checks name="fields" :label="_i('Dati Utenti')" :options="$options" :value="$currentgas->orders_shipping_user_columns" />

        <?php list($options, $values) = flaxComplexOptions(App\Formatters\Order::formattableColumns('shipping')) ?>
        <x-larastrap::checks name="fields" :label="_i('Colonne Prodotti')" :options="$options" :value="$currentgas->orders_shipping_product_columns" />

        <x-larastrap::radios name="status" :label="_i('Stato Prenotazioni')" :options="['pending' => _i('Prenotate'), 'shipped' => _i('Consegnate')]" value="pending" />

        <x-larastrap::radios name="format" :label="_i('Formato')" :options="['pdf' => _i('PDF'), 'csv' => _i('CSV')]" value="pdf" />
    </x-larastrap::form>
</x-larastrap::modal>
