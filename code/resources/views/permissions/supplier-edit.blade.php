<x-larastrap::modal :title="_i('Configura Ruoli per %s', $supplier->printableName())" classes="inner-modal">
    <input type="hidden" name="reload-portion" value="#permissions-list-{{ sanitizeId($supplier->id) }}">

	@php

	$class = get_class($supplier);
	$roles = $currentuser->managed_roles->filter(function($role) use ($class) {
		return $role->enabledClass($class);
	});

	@endphp

	@if($roles->isEmpty())
		<p class="alert alert-danger">
			{{ _i('Non sei autorizzato a gestire nessun ruolo.') }}
		</p>
	@else
	    @foreach($roles as $role)
	        <div class="card mb-4">
                <div class="card-header">{{ $role->name }}</div>
                <div class="card-body">
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
	        </div>
	    @endforeach
	@endif
</x-larastrap::modal>
