<input type="hidden" name="post-saved-refetch" value="#permissions-management">

<x-larastrap::text name="name" :label="_i('Nome')" required />
<x-larastrap::selectobj name="parent_id" :label="_i('Ruolo Superiore')" :options="App\Role::orderBy('name')->get()" :extraitem="_i('Nessuno')" :pophelp="_i('Gli utenti con assegnato il ruolo superiore potranno assegnare ad altri utenti questo ruolo')" />

@foreach(allPermissions() as $class => $permissions)
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
