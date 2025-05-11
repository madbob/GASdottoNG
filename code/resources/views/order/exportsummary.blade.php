<x-larastrap::modal classes="close-on-submit order-document-download-modal">
    <x-larastrap::form method="GET" :action="url('orders/document/' . $order->id . '/summary')">
        <p>{!! __('export.help_aggregate_export_summary') !!}</p>
        <p>{!! __('export.help_csv_libreoffice') !!}</p>

        <hr/>

        @include('commons.selectshippingexport', ['aggregate' => $order->aggregate, 'included_metaplace' => ['no', 'all_by_place']])

        <?php list($options, $values) = flaxComplexOptions(App\Formatters\Order::formattableColumns('summary')) ?>
        <x-larastrap::checks name="fields" :label="_i('Colonne')" :options="$options" :value="$values" />

        <x-larastrap::radios name="status" :label="_i('Quantità')" :options="['pending' => _i('Prenotate'), 'shipped' => _i('Consegnate')]" value="pending" />
        @include('order.partials.export.modifiers', ['order' => $order])
        <x-larastrap::radios name="format" :label="_i('Formato')" :options="['pdf' => _i('PDF'), 'csv' => _i('CSV'), 'gdxp' => _i('GDXP')]" value="pdf" />

        @include('order.filesmail', ['contacts' => $order->supplier->involvedEmails()])
    </x-larastrap::form>
</x-larastrap::modal>
