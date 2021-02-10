<div id="permissions-management" class="gas-permission-editor" data-fetch-url="{{ route('roles.index') }}">
    <div class="page-header">
        <h3>{{ _i('Permessi') }}</h3>
    </div>

    <div class="row">
        <div class="col-md-6">
            <form class="form-horizontal auto-submit" method="PUT" action="{{ route('gas.update', $gas->id) }}">
                <input type="hidden" name="group" value="roles">

                @include('commons.selectobjfield', [
                    'obj' => $gas,
                    'name' => 'roles->user',
                    'label' => _i('Ruolo Utente non Privilegiato'),
                    'objects' => App\Role::all(),
                    'help_popover' => _i("Questo ruolo sarà automaticamete assegnato ad ogni nuovo utente"),
                ])

                @if(App\Role::someone('users.subusers'))
                    @include('commons.selectobjfield', [
                        'obj' => $gas,
                        'name' => 'roles->friend',
                        'label' => _i('Ruolo Sotto-Utente'),
                        'objects' => App\Role::all(),
                        'help_popover' => _i("Questo ruolo sarà automaticamente assegnato ad ogni \"amico\" degli utenti esistenti. Si consiglia di creare un ruolo dedicato, con permessi limitati alle sole prenotazioni"),
                    ])
                @endif
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            @include('commons.addingbutton', [
                'template' => 'permissions.base-edit',
                'typename' => 'role',
                'typename_readable' => _i('Ruolo'),
                'targeturl' => 'roles'
            ])
        </div>
    </div>

    <div class="clearfix"></div>
    <br/>

    <div class="row">
        <div class="col-md-12">
            @include('commons.loadablelist', [
                'identifier' => 'role-list',
                'items' => App\Role::sortedByHierarchy()
            ])
        </div>
    </div>
</div>
