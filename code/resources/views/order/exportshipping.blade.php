<x-larastrap::modal :title="_i('Dettaglio Consegne')" classes="close-on-submit order-document-download-modal">
    <x-larastrap::form classes="direct-submit" method="GET" :action="url('orders/document/' . $order->id . '/shipping')">
        <p>
            {{ _i("Da qui puoi ottenere un documento in cui si trovano le informazioni relative alle singole prenotazioni. Utile da consultare mentre si effettuano le consegne.") }}
        </p>
        <p>
            {!! _i("Per la consultazione e l'elaborazione dei files in formato CSV (<i>Comma-Separated Values</i>) si consiglia l'uso di <a target=\"_blank\" href=\"http://it.libreoffice.org/\">LibreOffice</a>.") !!}
        </p>

        <hr/>

        @include('commons.selectshippingexport', ['aggregate' => $order->aggregate, 'included_metaplace' => ['all_by_name', 'all_by_place']])

        <?php list($options, $values) = flaxComplexOptions(App\Formatters\User::formattableColumns('shipping')) ?>
        <x-larastrap::checks name="fields" :label="_i('Dati Utenti')" :options="$options" :value="$currentgas->orders_shipping_user_columns" />

        <?php list($options, $values) = flaxComplexOptions(App\Formatters\Order::formattableColumns('shipping')) ?>
        <x-larastrap::checks name="fields" :label="_i('Colonne Prodotti')" :options="$options" :value="$currentgas->orders_shipping_product_columns" />

        <x-larastrap::radios name="status" :label="_i('Stato Prenotazioni')" :options="['pending' => _i('Prenotate'), 'shipped' => _i('Consegnate')]" value="pending" />

        @include('order.partials.export.modifiers', ['order' => $order])

        @if(someoneCan('users.subusers'))
            <x-larastrap::radios name="isolate_friends" :label="_i('Amici separati')" :options="['0' => _i('No'), '1' => _i('Sì')]" value="0" :pophelp="_i('Di default, le prenotazioni degli utenti \'amici\' vengono aggregate in quelle dei rispettivi utenti principali. Selezionando \'Sì\', vengono rappresentate nel documento come prenotazioni autonome.')" />
        @endif

        <x-larastrap::radios name="format" :label="_i('Formato')" :options="['pdf' => _i('PDF'), 'csv' => _i('CSV')]" value="pdf" />

        @include('order.filesmail', ['contacts' => $order->supplier->involvedEmails()])
    </x-larastrap::form>
</x-larastrap::modal>
