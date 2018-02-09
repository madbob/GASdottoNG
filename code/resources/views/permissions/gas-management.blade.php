<div id="permissions-management" data-fetch-url="{{ url('roles') }}">
    <div class="page-header">
        <h3>{{ _i('Permessi') }}</h3>
    </div>

    <div class="row">
        <div class="col-md-6">
            <form class="form-horizontal auto-submit" method="PUT" action="{{ url('gas/' . $gas->id) }}">
                <input type="hidden" name="group" value="roles">

                @include('commons.selectobjfield', [
                    'obj' => $gas,
                    'name' => 'roles->user',
                    'label' => _i('Ruolo Utente non Privilegiato'),
                    'objects' => App\Role::all()
                ])

                @if(App\Role::someone('users.subusers'))
                    @include('commons.selectobjfield', [
                        'obj' => $gas,
                        'name' => 'roles->friend',
                        'label' => _i('Ruolo Sotto-Utente'),
                        'objects' => App\Role::all()
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
