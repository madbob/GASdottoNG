<div class="card-header">
    <h3>{{ _i('Permessi') }}</h3>
</div>
<div class="card-body">
    <div class="row">
        <div class="col-12 col-md-6">
            <?php $existing_roles = allRoles() ?>

            <x-larastrap::form classes="auto-submit" method="PUT" :action="route('gas.update', $gas->id)" :buttons="[]">
                <input type="hidden" name="group" value="roles">

                <x-larastrap::selectobj name="roles->user" :label="_i('Ruolo Utente non Privilegiato')" :options="$existing_roles" :value="roleByFunction('user')->id" :pophelp="_i('Questo ruolo sarà automaticamete assegnato ad ogni nuovo utente')" />

                @if(someoneCan('users.subusers'))
                    <x-larastrap::selectobj name="roles->friend" :label="_i('Ruolo Sotto-Utente')" :options="$existing_roles" :value="roleByFunction('friend')->id" :pophelp="_i('Questo ruolo sarà automaticamente assegnato ad ogni amico degli utenti esistenti. Si consiglia di creare un ruolo dedicato, con permessi limitati alle sole prenotazioni')" />
                @endif

                @if($currentgas->multigas)
                    <x-larastrap::selectobj name="roles->multigas" :label="_i('Ruolo Amministratore GAS Secondario')" :options="$existing_roles" :value="roleByFunction('multigas')->id" :pophelp="_i('Questo ruolo sarà automaticamente assegnato al primo utente di ogni nuovo GAS creato nel pannello Multi-GAS')" />
                @endif
            </x-larastrap::form>
        </div>
    </div>

    <div class="row mt-2">
        <div class="col">
            @include('commons.addingbutton', [
                'template' => 'permissions.base-edit',
                'typename' => 'role',
                'typename_readable' => _i('Ruolo'),
                'targeturl' => 'roles',
                'autoread' => true,
            ])

            <x-larastrap::downloading :label="_i('Esporta Permessi ')" :href="route('roles.export')" />
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
