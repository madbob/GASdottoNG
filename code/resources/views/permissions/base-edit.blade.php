<input type="hidden" name="post-saved-refetch" value="#permissions-management">

<x-larastrap::text name="name" tlabel="generic.name" required />
<x-larastrap::select-model name="parent" tlabel="permissions.parent_role" :options="allRoles()" :extra_options="[0 => __('generic.none')]" tpophelp="permissions.help.parent_role" />

<x-larastrap::field tlabel="permissions.name">
    @foreach(allPermissions() as $class => $permissions)
        <x-larastrap::checklist name="actions" :options="$permissions" squeeze />
    @endforeach
</x-larastrap::field>
