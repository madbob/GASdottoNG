<div class="card-header">
    {{ __('texts.permissions.name') }}
</div>
<div class="card-body">
    <div class="row">
        <div class="col-12 col-md-6">
            <?php $existing_roles = allRoles() ?>

            <x-larastrap::form classes="auto-submit" method="PUT" :action="route('gas.update', $gas->id)" :buttons="[]">
                <input type="hidden" name="group" value="roles">

                <x-larastrap::select-model name="roles->user" tlabel="permissions.unprivileged" :options="$existing_roles" :value="roleByFunction('user')->id" tpophelp="permissions.help.unprivileged" />

                @if(someoneCan('users.subusers'))
                    <x-larastrap::select-model name="roles->friend" tlabel="permissions.sub_user" :options="$existing_roles" :value="roleByFunction('friend')->id" tpophelp="permissions.help.sub_user" />
                @endif

                @if($currentgas->multigas)
                    <x-larastrap::select-model name="roles->multigas" tlabel="permissions.multigas_admin" :options="$existing_roles" :value="roleByFunction('multigas')->id" tpophelp="permissions.help.multigas_admin" />
                @endif
            </x-larastrap::form>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col">
            @include('commons.addingbutton', [
                'template' => 'permissions.base-edit',
                'typename' => 'role',
                'typename_readable' => __('texts.permissions.role'),
                'targeturl' => 'roles',
                'autoread' => true,
            ])

            <x-larastrap::downloading tlabel="generic.export" :href="route('roles.export')" />
        </div>
    </div>

    <div class="row mt-2">
        <div class="col">
            @include('commons.loadablelist', [
                'identifier' => 'role-list',
                'items' => App\Role::sortedByHierarchy()
            ])
        </div>
    </div>
</div>
