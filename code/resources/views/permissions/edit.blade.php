<div>
    <x-larastrap::mform :obj="$role" classes="role-editor" method="PUT" :action="route('roles.update', $role->id)" :nodelete="$role->users()->count() != 0 || $role->system == true" autoread>
        <div class="row">
            <div class="col-md-6">
                <x-larastrap::text name="name" tlabel="generic.name" required />

                <?php $super_candidates = App\Role::whereNotIn('id', $role->children->pluck('id')->toArray())->where('id', '!=', $role->id)->orderBy('name')->get() ?>
                @if($super_candidates->count() != 0)
                    <x-larastrap::select-model name="parent" tlabel="permissions.parent_role" :options="$super_candidates" :extra_options="[0 => __('texts.generic.none')]" />
                @endif
            </div>
        </div>

        <hr/>

        <div class="row">
            <div class="col-md-4">
                @foreach(allPermissions() as $class => $permissions)
                    <ul class="list-group mb-2">
                        @foreach($permissions as $identifier => $description)
                            <li class="list-group-item">
                                <?php $is_mandatory = $role->mandatoryAction($identifier) ?>
                                {{ $description }}
                                @if($is_mandatory)
                                    <br><small>{{ __('texts.permissions.help.unique_role_warning') }}</small>
                                @endif
                                <span class="float-end">
                                    <input type="checkbox" data-role="{{ $role->id }}" data-action="{{ $identifier }}" {{ $role->enabledAction($identifier) ? 'checked' : '' }} {{ $is_mandatory ? 'disabled' : '' }}>
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endforeach
            </div>
            <div class="col-md-4">
                <div class="d-flex align-items-start">
                    <ul class="nav flex-column nav-pills" role="tablist">
                        @foreach($role->users as $user)
                            <li class="nav-item" data-user="{{ $user->id }}">
                                <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#permissions-{{ sanitizeId($user->id) }}-{{ $role->id }}">
                                    {{ $user->printableName() }}
									@if($user->checkRoleTargets($role) == false)
										<span class="text-danger" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-content="{{ __('texts.permissions.help.missing_elements_warning') }}"><i class="bi-exclamation-circle"></i></span>
									@endif
                                </button>
                            </li>
                        @endforeach

                        <li class="nav-item last-tab">
                            <input class="form-control roleAssign" type="text" placeholder="{{ __('texts.permissions.add_user') }}">
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-md-4 tab-content role-users">
                @foreach($role->users as $user)
                    @include('permissions.main_roleuser', [
                        'role' => $role,
                        'user' => $user
                    ])
                @endforeach
            </div>
        </div>
	</x-larastrap::mform>

    @stack('postponed')
</div>
