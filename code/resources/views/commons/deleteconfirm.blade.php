<x-larastrap::modal id="delete-confirm-modal" :title="_i('Elimina')">
    <x-larastrap::iform method="POST" id="form-delete-confirm-modal" :buttons="[['type' => 'submit', 'color' => 'danger', 'label' => _i('Conferma')]]">
        <input type="hidden" name="_method" value="delete">

        @if($password_protected)
            <input type="hidden" name="pre-saved-function" value="passwordProtected">
        @endif

        @include('commons.extrafields')

        <div class="alert alert-danger">
            {{ _i('Sei sicuro di voler eliminare questo elemento?') }}
        </div>
    </x-larastrap::iform>
</x-larastrap::modal>
