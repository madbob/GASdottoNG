<x-larastrap::mform :obj="$role" classes="role-editor" method="PUT" :action="route('roles.update', $role->id)" :nodelete="$role->users()->count() != 0">
    <div class="row">
        <div class="col-md-6">
            <x-larastrap::text name="name" :label="_i('Nome')" required />
            <x-larastrap::selectobj name="parent_id" :label="_i('Ruolo Superiore')" :options="App\Role::orderBy('name')->get()" :extraitem="_i('Nessuno')" />
        </div>
    </div>

    <hr/>

    <div class="row">
        <div class="col-md-4">
            @foreach(App\Role::allPermissions() as $class => $permissions)
                <ul class="list-group mb-2">
                    @foreach($permissions as $identifier => $description)
                        <li class="list-group-item">
                            {{ $description }}
                            <span class="float-end">
                                <input type="checkbox" data-role="{{ $role->id }}" data-action="{{ $identifier }}" {{ $role->enabledAction($identifier) ? 'checked' : '' }}>
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endforeach
        </div>
        <div class="col-md-4">
            <div class="d-flex align-items-start">
                <ul class="nav flex-column nav-pills">
                    @foreach($role->users as $user)
                        <li class="nav-item" data-user="{{ $user->id }}">
                            <button type="button" class="nav-link" data-bs-toggle="tab" data-bs-target="#permissions-{{ sanitizeId($user->id) }}-{{ $role->id }}">
                                {{ $user->printableName() }}
                            </button>
                        </li>
                    @endforeach

                    <li class="nav-item last-tab">
                        <input class="form-control roleAssign" type="text" placeholder="{{ _i('Aggiungi Nuovo Utente') }}">
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
</x-larastrap::form>

@stack('postponed')
