<form class="form-horizontal main-form role-editor" method="PUT" action="{{ route('roles.update', $role->id) }}" data-target="role-editor-{{ rand() }}">
    <input type="hidden" name="post-saved-refetch" value="#permissions-management">

    <div class="row">
        <div class="col-md-6">
            @include('commons.textfield', [
                'obj' => $role,
                'name' => 'name',
                'label' => _i('Nome')
            ])
            @include('commons.boolfield', [
                'obj' => $role,
                'name' => 'always',
                'label' => _i('Abilitato di Default')
            ])
        </div>
        <div class="col-md-6">
            @include('commons.selectobjfield', [
                'obj' => $role,
                'name' => 'parent_id',
                'objects' => App\Role::orderBy('name')->get(),
                'label' => _i('Ruolo Superiore'),
                'extra_selection' => [
                    '0' => _i('Nessuno')
                ]
            ])
        </div>
    </div>

    <hr/>

    <div class="row">
        <div class="col-md-4">
            @foreach(App\Role::allPermissions() as $class => $permissions)
                <ul class="list-group">
                    @foreach($permissions as $identifier => $description)
                        <li class="list-group-item">
                            {{ $description }}
                            <span class="pull-right">
                                <input type="checkbox" data-toggle="toggle" data-size="mini" data-role="{{ $role->id }}" data-action="{{ $identifier }}" {{ $role->enabledAction($identifier) ? 'checked' : '' }}>
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endforeach
        </div>
        <div class="col-md-4 dense-list">
            <ul class="nav nav-tabs tabs-left role-users" role="tablist">
                @foreach($role->users as $user)
                    <li class="presentation" data-user="{{ $user->id }}">
                        <a href="#permissions-{{ $user->id }}-{{ $role->id }}" aria-controls="#permissions-{{ $user->id }}-{{ $role->id }}" role="tab" data-toggle="tab">
                            {{ $user->printableName() }}
                        </a>
                    </li>
                @endforeach

                <li class="presentation last-tab">
                    <input class="form-control roleAssign" type="text" placeholder="{{ _i('Aggiungi Nuovo Utente') }}">
                </li>
            </ul>
        </div>
        <div class="col-md-4 tab-content role-users dense-list">
            @foreach($role->users as $user)
                @include('permissions.main_roleuser', [
                    'role' => $role,
                    'user' => $user
                ])
            @endforeach
        </div>
    </div>

    {{--
        I ruoli adibiti alla modifica dei permessi non possono essere rimossi.
        Questo per evitare che vengano cancellati per sbaglio ruoli
        indispensabili al funzionamento!
    --}}
    @include('commons.formbuttons', ['no_delete' => $role->enabledAction('gas.permissions')])
</form>

@stack('postponed')
