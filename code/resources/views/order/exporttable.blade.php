<x-larastrap::modal :title="_i('Tabella Complessiva Prodotti')" classes="close-on-submit">
    <x-larastrap::form classes="direct-submit" method="GET" :action="url('orders/document/' . $order->id . '/table')" label_width="2" input_width="10">
        <p>
            {{ _i("Da qui puoi ottenere un documento CSV coi dettagli di tutti i prodotti prenotati in quest'ordine.") }}
        </p>
        <p>
            {!! _i("Per la consultazione e l'elaborazione dei files in formato CSV (<i>Comma-Separated Values</i>) si consiglia l'uso di <a target=\"_blank\" href=\"http://it.libreoffice.org/\">LibreOffice</a>.") !!}
        </p>

        <hr/>

        @if($currentgas->hasFeature('shipping_places'))
            <?php

            $options = [
                'all_by_name' => _i('Tutti (ordinati per utente)'),
                'all_by_place' => _i('Tutti (ordinati per luogo)'),
            ];

            foreach($currentgas->deliveries as $delivery) {
                $options[$delivery->id] = $delivery->name;
            }

            ?>
            <x-larastrap::radios name="shipping_place" :label="_i('Luogo di Consegna')" :options="$options" value="all_by_place" />
        @endif

        <x-larastrap::radios name="status" :label="_i('Stato Prenotazioni')" :options="['booked' => _i('Prenotate'), 'delivered' => _i('Consegnate'), 'saved' => _i('Salvate')]" value="booked" />
    </x-larastrap::form>
</x-larastrap::modal>
