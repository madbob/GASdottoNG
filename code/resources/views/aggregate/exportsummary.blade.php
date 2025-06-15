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

<x-larastrap::modal classes="order-document-download-modal">
    <x-larastrap::form classes="direct-submit" method="GET" :action="route('aggregates.document', ['id' => $aggregate->id, 'type' => 'summary'])">
        <p>{!! __('texts.export.help_aggregate_export_summary') !!}</p>
        <p>{!! __('texts.export.help_csv_libreoffice') !!}</p>

        <hr/>

        <input type="hidden" name="managed_gas" value="{{ $managed_gas }}">

        @include('commons.selectshippingexport', ['aggregate' => $aggregate, 'included_metaplace' => ['no', 'all_by_place']])

        <?php list($options, $values) = flaxComplexOptions(App\Formatters\Order::formattableColumns('summary')) ?>
        <x-larastrap::checks name="fields" tlabel="export.data.columns" :options="$options" :value="$values" />

        <x-larastrap::radios name="status" tlabel="export.data.status" :options="['pending' => __('texts.orders.booking.statuses.booked'), 'shipped' => __('texts.orders.booking.statuses.shipped')]" value="pending" />
        <x-larastrap::radios name="format" tlabel="export.data.format" :options="['pdf' => __('texts.export.data.formats.pdf'), 'csv' => __('texts.export.data.formats.csv'), 'gdxp' => __('texts.export.data.formats.gdxp')]" value="pdf" />
    </x-larastrap::form>
</x-larastrap::modal>
