<x-larastrap::modal :title="_i('Tabella Complessiva Prodotti')" classes="close-on-submit order-document-download-modal">
    <x-larastrap::form method="GET" :action="url('orders/document/' . $order->id . '/table')" label_width="2" input_width="10">
        <p>
            {{ _i("Da qui puoi ottenere un documento CSV coi dettagli di tutti i prodotti prenotati in quest'ordine.") }}
        </p>
		<p>
            {{ _i("Se intendi utilizzare questo documento con la funzione 'Consegne -> Importa CSV', per importare le quantità consegnate dopo averle elaborate manualmente, ti raccomandiamo di includere nell'esportazione anche lo Username degli utenti coinvolti.") }}
        </p>
        <p>
            {!! _i("Per la consultazione e l'elaborazione dei files in formato CSV (<i>Comma-Separated Values</i>) si consiglia l'uso di <a target=\"_blank\" href=\"http://it.libreoffice.org/\">LibreOffice</a>.") !!}
        </p>

        <hr/>

        @include('commons.selectshippingexport', ['aggregate' => $order->aggregate, 'included_metaplace' => ['all_by_name', 'all_by_place']])

        <?php list($options, $values) = flaxComplexOptions(App\Formatters\User::formattableColumns()) ?>
        <x-larastrap::checks name="fields" :label="_i('Dati Utenti')" :options="$options" :value="$currentgas->orders_shipping_user_columns" />

        <x-larastrap::radios name="status" :label="_i('Stato Prenotazioni')" :options="['booked' => _i('Prenotate'), 'delivered' => _i('Consegnate'), 'saved' => _i('Salvate')]" value="booked" />

        @include('order.filesmail', ['contacts' => $order->supplier->involvedEmails()])
    </x-larastrap::form>
</x-larastrap::modal>
