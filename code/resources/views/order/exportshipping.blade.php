<x-larastrap::modal classes="close-on-submit order-document-download-modal">
    <x-larastrap::form classes="direct-submit" method="GET" :action="url('orders/document/' . $order->id . '/shipping')">
        <p>{{ __('texts.orders.help_order_export_shipping') }}</p>

        <hr/>

        @include('commons.selectshippingexport', ['aggregate' => $order->aggregate, 'included_metaplace' => ['all_by_name', 'all_by_place', 'specific']])

        <?php list($options, $values) = flaxComplexOptions(App\Formatters\User::formattableColumns('shipping')) ?>
        <x-larastrap::checks name="fields" tlabel="export.data.users" :options="$options" :value="$currentgas->orders_shipping_user_columns" />

        <?php list($options, $values) = flaxComplexOptions(App\Formatters\Order::formattableColumns('shipping')) ?>
        <x-larastrap::checks name="fields" tlabel="export.data.products" :options="$options" :value="$currentgas->orders_shipping_product_columns" />

        <x-larastrap::radios name="status" tlabel="export.data.status" :options="['pending' => __('texts.orders.booking.statuses.booked'), 'shipped' => __('texts.orders.booking.statuses.shipped')]" value="pending" />

        @include('order.partials.export.modifiers', ['order' => $order])

        @if(someoneCan('users.subusers'))
            <x-larastrap::radios name="isolate_friends" tlabel="export.data.split_friends" :options="['0' => __('texts.generic.no'), '1' => __('texts.generic.yes')]" value="0" tpophelp="export.help_split_friends" />
        @endif

        <x-larastrap::radios name="format" tlabel="export.data.format" :options="['pdf' => __('texts.export.data.formats.pdf'), 'csv' => __('texts.export.data.formats.csv')]" value="pdf" />

        @include('order.filesmail', ['contacts' => $order->supplier->involvedEmails()])
    </x-larastrap::form>
</x-larastrap::modal>
