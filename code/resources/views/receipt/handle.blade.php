<x-larastrap::modal :title="_i('Scarica o Inoltra')" classes="order-document-download-modal">
    <x-larastrap::form classes="direct-submit" method="GET" :action="route('receipts.download', $receipt->id)">
        <p>
            {{ _i("Scarica la fattura generata, o inoltrala via email.") }}
        </p>

        <hr/>

        <x-larastrap::check name="send_mail" :label="_i('Inoltra Mail')" />
    </x-larastrap::form>
</x-larastrap::modal>
