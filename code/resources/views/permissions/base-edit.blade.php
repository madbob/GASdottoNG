<input type="hidden" name="post-saved-refetch" value="#role-list" data-fetch-url="{{ route('roles.index') }}">

<x-larastrap::text name="name" :label="_i('Nome')" required />
<x-larastrap::check name="always" :label="_i('Abilitato di Default')" />
<x-larastrap::selectobj name="parent_id" :label="_i('Ruolo Superiore')" :options="App\Role::orderBy('name')->get()" :extraitem="_i('Nessuno')" :pophelp="_i('Gli utenti con assegnato il ruolo superiore potranno assegnare ad altri utenti questo ruolo')" />

@foreach(App\Role::allPermissions() as $class => $permissions)
    <ul class="list-group mt-2">
        @foreach($permissions as $identifier => $description)
            <li class="list-group-item">
                {{ $description }}
                <span class="float-end">
                    <input type="checkbox" name="actions[]" value="{{ $identifier }}">
                </span>
            </li>
        @endforeach
    </ul>
@endforeach
