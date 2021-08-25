<x-larastrap::modal :title="_i('Scarica o Inoltra')" classes="order-document-download-modal">
    <x-larastrap::field :label="_i('Scarica')">
        <a class="btn btn-light" href="{{ route('receipts.download', $receipt->id) }}">{{ _i('Clicca Qui') }} <i class="bi-download"></i></a>
    </x-larastrap::field>

    <x-larastrap::field :label="_i('Inoltra')">
        <form class="modal-form" method="GET" action="{{ route('receipts.download', $receipt->id) }}">
            <input type="hidden" name="send_mail" value="1">
            <input type="hidden" name="close-modal" value="1">
            <button type="submit" class="btn btn-light">{{ _i('Inoltra via E-Mail') }}</button>
        </form>
    </x-larastrap::field>
</x-larastrap::modal>
