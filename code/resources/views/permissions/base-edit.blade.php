<input type="hidden" name="post-saved-refetch" value="#permissions-management">

<x-larastrap::text name="name" :label="_i('Nome')" required />
<x-larastrap::select-model name="parent" :label="_i('Ruolo Superiore')" :options="allRoles()" :extra_options="[0 => _i('Nessuno')]" :pophelp="_i('Gli utenti con assegnato il ruolo superiore potranno assegnare ad altri utenti questo ruolo')" />

<x-larastrap::field :label="_i('Permessi')">
    @foreach(allPermissions() as $class => $permissions)
        <x-larastrap::checklist name="actions" :options="$permissions" squeeze />
    @endforeach
</x-larastrap::field>
