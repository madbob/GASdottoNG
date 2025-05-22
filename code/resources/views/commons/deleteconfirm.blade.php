<x-larastrap::modal id="delete-confirm-modal" size="lg">
    <x-larastrap::iform method="DELETE" :action="$url ?? ''" id="form-delete-confirm-modal" :buttons="[['type' => 'submit', 'color' => 'danger', 'label' => _i('Conferma')]]">
        @if($password_protected)
            <input type="hidden" name="pre-saved-function" value="passwordProtected">
        @endif

        <input type="hidden" name="close-modal" value="1">
        @include('commons.extrafields')

        @php

        if (isset($text) == false) {
            $text = _i('Sei sicuro di voler eliminare questo elemento?');
        }

        @endphp

        {!! $text !!}
    </x-larastrap::iform>
</x-larastrap::modal>
