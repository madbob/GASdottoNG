<div role="tabpanel" class="tab-pane fade" id="permissions-{{ sanitizeId($user->id) }}-{{ $role->id }}">
    <div class="row">
        <div class="col">
            <ul class="list-group">
                @php

                $r = $user->roles()->where('roles.id', $role->id)->first();
                $targets = $role->targets;
                $last_class = null;

                @endphp

                @foreach($targets as $target)
                    @if ($last_class != get_class($target))
						@php
						$last_class = get_class($target);
						$last_applies_all = null;
						@endphp

						@if($targets->count() > 1)
	                        <li class="list-group-item list-group-item-danger">
	                            {{ __('generic.composable_all', ['label' => $last_class::commonClassName()]) }}<br/>
	                            <small>
	                                {{ __('permissions.help.global_permission_notice') }}
	                            </small>
	                            <span class="float-end">
	                                <input type="checkbox" class="all-{{ $user->id }}-{{ $role->id }}" data-user="{{ $user->id }}" data-role="{{ $role->id }}" data-target-id="*" data-target-class="{{ $last_class }}" {{ $r->appliesAll($last_class) ? 'checked' : '' }}>
	                            </span>
	                        </li>
						@else
							@php
							$last_applies_all = $r->appliesAll($last_class);
							@endphp
						@endif
                    @endif

                    <li class="list-group-item">
                        {{ $target->printableName() }}
                        <span class="float-end">
							<input type="checkbox" data-user="{{ $user->id }}" data-role="{{ $role->id }}" data-target-id="{{ $target->id }}" data-target-class="{{ get_class($target) }}" {{ $r->appliesOnly($target) || $last_applies_all ? 'checked' : '' }} {{ $user->id == $currentuser->id && $target->id == $currentgas->id ? 'disabled' : '' }}>
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col">
            @if($role->enabledAction('gas.permissions') && $user->id == $currentuser->id)
                <x-larastrap::suggestion>
                    {{ __('permissions.help.blocked_autoremove') }}
                </x-larastrap::suggestion>
            @else
                <button class="btn btn-danger remove-role" data-role="{{ $role->id }}" data-user="{{ $user->id }}">{{ __('permissions.revoke') }} {{ $role->name }} a {{ $user->printableName() }}</button>
            @endif
        </div>
    </div>
</div>
