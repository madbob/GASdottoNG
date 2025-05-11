<x-larastrap::modal classes="close-on-submit order-document-download-modal">
    <x-larastrap::form method="GET" :action="route('aggregates.document', ['id' => $aggregate->id, 'type' => 'table'])">
        <p>{!! __('orders.help_aggregate_export_table') !!}</p>
        <p>{!! __('orders.help_aggregate_export_table_for_delivery') !!}</p>
        <p>{!! __('export.help_csv_libreoffice') !!}</p>

        <hr/>

        @include('commons.selectshippingexport', ['aggregate' => $aggregate, 'included_metaplace' => ['all_by_name', 'all_by_place']])

        <?php list($options, $values) = flaxComplexOptions(App\Formatters\User::formattableColumns()) ?>
        <x-larastrap::checks name="fields" tlabel="export.data.users" :options="$options" :value="$currentgas->orders_shipping_user_columns" />

        <x-larastrap::radios name="status" tlabel="export.data.status" :options="['pending' => __('orders.booking.statuses.booked'), 'shipped' => __('orders.booking.statuses.shipped'), 'saved' => __('orders.booking.statuses.saved')]" value="pending" />
        <x-larastrap::radios name="include_missing" tlabel="export.flags.include_unbooked" :options="['yes' => __('generic.yes'), 'no' => __('generic.no')]" value="no" />
    </x-larastrap::form>
</x-larastrap::modal>
