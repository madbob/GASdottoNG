<x-larastrap::modal :title="_i('Configura Ruoli per %s', $supplier->printableName())" classes="inner-modal">
    <input type="hidden" name="reload-portion" value="#permissions-list-{{ sanitizeId($supplier->id) }}">

    @foreach($currentuser->managed_roles as $role)
        @if($role->enabledClass(get_class($supplier)))
            <div class="row">
                <h3>{{ $role->name }}</h3>
            </div>

            <div class="row mb-2">
                @include('commons.completionrows', [
                    'objects' => $role->usersByTarget($supplier),
                    'source' => route('users.search'),
                    'adding_label' => _i('Aggiungi Nuovo Utente'),
                    'add_callback' => 'supplierAttachUser',
                    'remove_callback' => 'supplierDetachUser',
                    'extras' => [
                        'supplier-id' => $supplier->id,
                        'role-id' => $role->id
                    ]
                ])
            </div>
        @endif
    @endforeach
</x-larastrap::modal>
