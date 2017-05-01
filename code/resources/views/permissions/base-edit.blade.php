@include('commons.textfield', ['obj' => $role, 'name' => 'name', 'label' => 'Nome'])
@include('commons.boolfield', ['obj' => $role, 'name' => 'always', 'label' => 'Abilitato di Default'])

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
