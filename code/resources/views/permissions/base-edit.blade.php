<input type="hidden" name="post-saved-refetch" value="#permissions-management">

<x-larastrap::text name="name" :label="_i('Nome')" required />
<x-larastrap::selectobj name="parent_id" :label="_i('Ruolo Superiore')" :options="allRoles()" :extraitem="_i('Nessuno')" :pophelp="_i('Gli utenti con assegnato il ruolo superiore potranno assegnare ad altri utenti questo ruolo')" />

<x-larastrap::field :label="_i('Permessi')">
    @foreach(allPermissions() as $class => $permissions)
        <x-larastrap::checklist name="actions" :options="$permissions" squeeze />
    @endforeach
</x-larastrap::field>
