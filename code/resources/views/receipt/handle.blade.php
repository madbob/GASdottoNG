<x-larastrap::modal classes="order-document-download-modal">
    <x-larastrap::field tlabel="generic.download">
        <a class="btn btn-light" href="{{ route('receipts.download', $receipt->id) }}">{{ __('texts.generic.click_here') }} <i class="bi-download"></i></a>
    </x-larastrap::field>

    <x-larastrap::field tlabel="generic.forward">
        <form class="modal-form" method="GET" action="{{ route('receipts.download', $receipt->id) }}">
            <input type="hidden" name="send_mail" value="1">
            <input type="hidden" name="close-modal" value="1">
            <button type="submit" class="btn btn-light">{{ __('texts.generic.forward') }}</button>
        </form>
    </x-larastrap::field>
</x-larastrap::modal>
