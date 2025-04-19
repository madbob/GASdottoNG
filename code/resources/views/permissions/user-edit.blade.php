<x-larastrap::modal :title="_i('Configura Ruoli per %s', [$user->printableName()])" classes="inner-modal">
    <input type="hidden" name="reload-portion" value="#permissions-list-{{ sanitizeId($user->id) }}">

	@if($currentuser->managed_roles->isEmpty())
		<p class="alert alert-danger">
			{{ _i('Non sei autorizzato a gestire nessun ruolo.') }}
		</p>
	@else
	    <div class="role-editor">
	        @foreach($currentuser->managed_roles as $role)
	            <?php

	            $urole = $user->roles()->where('roles.id', $role->id)->first();
	            $targets = $role->targets;
	            $last_class = null;

	            if ($targets->isEmpty()) {
	                continue;
	            }

	            ?>

	            <div class="card mb-4">
	                <div class="card-header">{{ $role->name }}</div>
                    <div class="card-body">
    	                @foreach($targets as $target)
    	                    @if ($targets->count() > 1 && $last_class != get_class($target))
    	                        <?php $last_class = get_class($target) ?>
    	                        <div class="col-md-4 alert-danger">
    	                            <div class="checkbox">
    	                                <label>
                                           <input type="checkbox" class="all-{{ $user->id }}-{{ $role->id }}" data-user="{{ $user->id }}" data-role="{{ $role->id }}" data-target-id="*" data-target-class="{{ $last_class }}" {{ $urole && $urole->appliesAll($last_class) ? 'checked' : '' }}> Tutti ({{ $last_class::commonClassName() }})
    	                                </label>
    	                            </div>
    	                        </div>
    	                    @endif

    	                    <div class="col-md-4">
    	                        <div class="checkbox">
    	                            <label>
                                        @if($role->enabledAction('gas.permissions') && $user->id == $currentuser->id && $urole && $urole->appliesOnly($target))
                                            <input disabled type="checkbox" {{ $urole && $urole->appliesOnly($target) ? 'checked' : '' }}> {{ $target->printableName() }}<br><small>{{ _i('Non puoi auto-revocarti questo ruolo amministrativo') }}</small>
                                        @else
    	                                   <input type="checkbox" data-role="{{ $role->id }}" data-user="{{ $user->id }}" data-target-id="{{ $target->id }}" data-target-class="{{ get_class($target) }}" {{ $urole && $urole->appliesOnly($target) ? 'checked' : '' }}> {{ $target->printableName() }}
                                       @endif
    	                            </label>
    	                        </div>
    	                    </div>
    	                @endforeach
                    </div>
	            </div>
	        @endforeach
	    </div>
	@endif
</x-larastrap::modal>
