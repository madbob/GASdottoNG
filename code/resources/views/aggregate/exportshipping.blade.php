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

<x-larastrap::modal>
    <x-larastrap::form classes="direct-submit" method="GET" :action="route('aggregates.document', ['id' => $aggregate->id, 'type' => 'shipping'])">
        <p>{{ __('orders.help_aggregate_export_shipping') }}</p>

        <hr>

        <input type="hidden" name="managed_gas" value="{{ $managed_gas }}">

        @include('commons.selectshippingexport', ['aggregate' => $aggregate, 'included_metaplace' => ['all_by_name', 'all_by_place', 'specific']])

        <?php list($options, $values) = flaxComplexOptions(App\Formatters\User::formattableColumns('shipping')) ?>
        <x-larastrap::checks name="fields" tlabel="export.data.users" :options="$options" :value="$currentgas->orders_shipping_user_columns" />

        <?php list($options, $values) = flaxComplexOptions(App\Formatters\Order::formattableColumns('shipping')) ?>
        <x-larastrap::checks name="fields" tlabel="export.data.products" :options="$options" :value="$currentgas->orders_shipping_product_columns" />

        <x-larastrap::radios name="status" tlabel="export.data.status" :options="['pending' => __('orders.booking.statuses.booked'), 'shipped' => __('orders.booking.statuses.shipped')]" value="pending" />

        @if(someoneCan('users.subusers'))
            <x-larastrap::radios name="isolate_friends" tlabel="export.data.split_friends" :options="['0' => __('generic.no'), '1' => __('generic.yes')]" value="0" tpophelp="export.help_split_friends" />
        @endif

        <x-larastrap::radios name="format" tlabel="export.data.format" :options="['pdf' => __('export.data.formats.pdf'), 'csv' => __('export.data.formats.csv')]" value="pdf" />
    </x-larastrap::form>
</x-larastrap::modal>
