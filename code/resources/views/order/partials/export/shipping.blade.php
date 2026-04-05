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
        'type' => 'shipping',
    ]);

    $forward = true;
    $aggregate = $order->aggregate;
}
else {
    $action = route('aggregates.document', [
        'id' => $order->id,
        'type' => 'shipping',
    ]);

    $forward = false;
    $aggregate = $order;
}

@endphp

<x-larastrap::modal classes="close-on-submit order-document-download-modal">
    <x-larastrap::form classes="direct-submit" method="GET" :action="$action">
        <p>{{ __('texts.orders.help_order_export_shipping') }}</p>

        <hr/>

        <input type="hidden" name="managed_gas" value="{{ $managed_gas }}">

        @include('commons.selectshippingexport', [
            'aggregate' => $aggregate,
            'included_metaplace' => ['all_by_name', 'all_by_place', 'specific']
        ])

        <?php list($options, $values) = flaxComplexOptions(App\Formatters\User::formattableColumns('shipping')) ?>
        <x-larastrap::checks name="fields" tlabel="export.data.users" :options="$options" :value="$currentgas->orders_shipping_user_columns" />

        <?php list($options, $values) = flaxComplexOptions(App\Formatters\Order::formattableColumns('shipping')) ?>
        <x-larastrap::checks name="fields" tlabel="export.data.products" :options="$options" :value="$currentgas->orders_shipping_product_columns" />

        <x-larastrap::radios name="status" tlabel="export.data.status" :options="[
            'pending' => __('texts.orders.booking.statuses.booked'),
            'shipped' => __('texts.orders.booking.statuses.shipped')
        ]" value="pending" />

        @include('order.partials.export.modifiers', [
            'order' => $order
        ])

        @if(someoneCan('users.subusers'))
            <x-larastrap::radios name="isolate_friends" tlabel="export.data.split_friends" :options="[
                '0' => __('texts.generic.no'),
                '1' => __('texts.generic.yes')
            ]" :value="$currentgas->orders_shipping_separate_friends ? 1 : 0" tpophelp="export.help_split_friends" />
        @endif

        <x-larastrap::radios name="format" tlabel="export.data.format" :options="[
            'pdf' => __('texts.export.data.formats.pdf'),
            'csv' => __('texts.export.data.formats.csv')
        ]" value="pdf" />

        @if($forward)
            @include('order.filesmail', ['contacts' => $order->supplier->involvedEmails()])
        @endif
    </x-larastrap::form>
</x-larastrap::modal>
