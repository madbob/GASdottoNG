<div id="permissions-management" class="card gas-permission-editor" data-fetch-url="{{ route('roles.index') }}">
    <div class="card-header">
        <h3>{{ _i('Permessi') }}</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-12 col-md-6">
                <x-larastrap::form classes="auto-submit" method="PUT" :action="route('gas.update', $gas->id)" :buttons="[]">
                    <input type="hidden" name="group" value="roles">

                    <x-larastrap::selectobj name="roles->user" :label="_i('Ruolo Utente non Privilegiato')" :options="App\Role::all()" :value="$gas->roles['user']" :pophelp="_i('Questo ruolo sarà automaticamete assegnato ad ogni nuovo utente')" />

                    @if(App\Role::someone('users.subusers'))
                        <x-larastrap::selectobj name="roles->friend" :label="_i('Ruolo Sotto-Utente')" :options="App\Role::all()" :value="$gas->roles['friend'] ?? ''" :pophelp="_i('Questo ruolo sarà automaticamente assegnato ad ogni amico degli utenti esistenti. Si consiglia di creare un ruolo dedicato, con permessi limitati alle sole prenotazioni')" />
                    @endif

                    @if(App\Role::someone('gas.multi'))
                        <x-larastrap::selectobj name="roles->multigas" :label="_i('Ruolo Amministratore Multi-GAS')" :options="App\Role::all()" :value="$gas->roles['multigas'] ?? ''" :pophelp="_i('Questo ruolo sarà automaticamente assegnato al primo utente di ogni nuovo GAS creato nel pannello Multi-GAS')" />
                    @endif
                </x-larastrap::form>
            </div>
        </div>

        <div class="row">
            <div class="col">
                @include('commons.addingbutton', [
                    'template' => 'permissions.base-edit',
                    'typename' => 'role',
                    'typename_readable' => _i('Ruolo'),
                    'targeturl' => 'roles'
                ])
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
</div>
