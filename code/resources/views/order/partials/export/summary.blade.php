@php

use App\Order;
use App\Aggregate;

$hub = app()->make('GlobalScopeHub');
if ($hub->enabled() == false) {
    $managed_gas = 0;
}
else {
    $managed_gas = $hub->getGasObj()->id;
}

if (is_a($order, Order::class)) {
    $action = route('orders.document', [
        'id' => $order->id,
        'type' => 'summary',
    ]);

    $forward = true;
    $aggregate = $order->aggregate;
}
else {
    $action = route('aggregates.document', [
        'id' => $order->id,
        'type' => 'summary',
    ]);

    $forward = false;
    $aggregate = $order;
}

@endphp

<x-larastrap::modal classes="close-on-submit order-document-download-modal">
    <x-larastrap::form method="GET" :action="$action">
        <p>{!! __('texts.export.help_aggregate_export_summary') !!}</p>
        <p>{!! __('texts.export.help_csv_libreoffice') !!}</p>

        <hr/>

        <input type="hidden" name="managed_gas" value="{{ $managed_gas }}">

        @include('commons.selectshippingexport', [
            'aggregate' => $aggregate,
            'included_metaplace' => ['no', 'all_by_place']
        ])

        <?php list($options, $values) = flaxComplexOptions(App\Formatters\Order::formattableColumns('summary')) ?>
        <x-larastrap::checks name="fields" tlabel="export.data.columns" :options="$options" :value="$values" />

        <x-larastrap::radios name="status" tlabel="generic.quantity" :options="[
            'pending' => __('texts.orders.booking.statuses.booked'),
            'shipped' => __('texts.orders.booking.statuses.shipped')
        ]" value="pending" />

        @include('order.partials.export.modifiers', ['order' => $order])

        <x-larastrap::radios name="format" tlabel="export.data.format" :options="[
            'pdf' => __('texts.export.data.formats.pdf'),
            'csv' => __('texts.export.data.formats.csv'),
            'gdxp' => __('texts.export.data.formats.gdxp')
        ]" value="pdf" />

        @if($forward)
            @include('order.filesmail', ['contacts' => $order->supplier->involvedEmails()])
        @endif
    </x-larastrap::form>
</x-larastrap::modal>
