<x-larastrap::modal classes="close-on-submit order-document-download-modal">
    <x-larastrap::form method="GET" :action="url('orders/document/' . $order->id . '/summary')">
        <p>{!! __('export.help_aggregate_export_summary') !!}</p>
        <p>{!! __('export.help_csv_libreoffice') !!}</p>

        <hr/>

        @include('commons.selectshippingexport', ['aggregate' => $order->aggregate, 'included_metaplace' => ['no', 'all_by_place']])

        <?php list($options, $values) = flaxComplexOptions(App\Formatters\Order::formattableColumns('summary')) ?>
        <x-larastrap::checks name="fields" tlabel="export.data.columns" :options="$options" :value="$values" />

        <x-larastrap::radios name="status" tlabel="generic.quantity" :options="[
            'pending' => __('orders.booking.statuses.booked'),
            'shipped' => __('orders.booking.statuses.shipped')
        ]" value="pending" />

        @include('order.partials.export.modifiers', ['order' => $order])

        <x-larastrap::radios name="format" tlabel="export.data.format" :options="[
            'pdf' => __('export.data.formats.pdf'),
            'csv' => __('export.data.formats.csv'),
            'gdxp' => __('export.data.formats.gdxp')
        ]" value="pdf" />

        @include('order.filesmail', ['contacts' => $order->supplier->involvedEmails()])
    </x-larastrap::form>
</x-larastrap::modal>
