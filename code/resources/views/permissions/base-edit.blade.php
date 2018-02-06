<input type="hidden" name="post-saved-refetch" value="#role-list" data-fetch-url="{{ url('roles') }}">

@include('commons.textfield', [
    'obj' => $role,
    'name' => 'name',
    'label' => _i('Nome'),
    'mandatory' => true
])

@include('commons.boolfield', [
    'obj' => $role,
    'name' => 'always',
    'label' => _i('Abilitato di Default')
])

@include('commons.selectobjfield', [
    'obj' => $role,
    'name' => 'parent_id',
    'objects' => App\Role::orderBy('name')->get(),
    'label' => _i('Ruolo Superiore'),
    'extra_selection' => [
        '0' => _i('Nessuno')
    ]
])

@foreach(App\Role::allPermissions() as $class => $permissions)
    <ul class="list-group">
        @foreach($permissions as $identifier => $description)
            <li class="list-group-item">
                {{ $description }}
                <span class="pull-right">
                    <input type="checkbox" data-toggle="toggle" data-size="mini" name="actions[]" value="{{ $identifier }}">
                </span>
            </li>
        @endforeach
    </ul>
@endforeach
