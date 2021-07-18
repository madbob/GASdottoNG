<x-larastrap::modal id="delete-confirm-modal" :title="_i('Elimina')">
    <x-larastrap::form :classes="$password_protected ? 'password-protected' : ''" method="POST" id="form-delete-confirm-modal" :buttons="[['type' => 'submit', 'color' => 'danger', 'label' => _i('Conferma')]]">
        <input type="hidden" name="_method" value="delete">
        @include('commons.extrafields')

        <div class="alert alert-danger">
            {{ _i('Sei sicuro di voler eliminare questo elemento?') }}
        </div>
    </x-larastrap::form>
</x-larastrap::modal>
