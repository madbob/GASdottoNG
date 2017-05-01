@extends($theme_layout)

@section('content')

<div class="row">
</div>

<div class="page-header">
    <h3>Configurazioni Generali</h3>
</div>

<form class="form-horizontal inner-form gas-editor" method="PUT" action="{{ url('gas/' . $gas->id) }}">
    <div class="row">
        <div class="col-md-6">
            @include('commons.textfield', ['obj' => $gas, 'name' => 'name', 'label' => 'Nome', 'mandatory' => true])
            @include('commons.textfield', ['obj' => $gas, 'name' => 'email', 'label' => 'E-Mail', 'mandatory' => true])
            @include('commons.textarea', ['obj' => $gas, 'name' => 'description', 'label' => 'Descrizione'])
            @include('commons.textarea', ['obj' => $gas, 'name' => 'message', 'label' => 'Messaggio Homepage'])

            @if(App\Role::someone('gas.access', $gas))
                @include('commons.boolfield', ['obj' => $gas, 'name' => 'restricted', 'label' => 'Modalità Manutenzione'])
            @endif
        </div>
        <div class="col-md-6">
            <div class="well">
                <div class="row">
                    <div class="col-md-6">
                        @include('commons.textfield', ['obj' => $gas, 'name' => 'mailusername', 'label' => 'Username'])
                    </div>
                    <div class="col-md-6">
                        @include('commons.passwordfield', ['obj' => $gas, 'name' => 'mailpassword', 'label' => 'Password'])
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        @include('commons.textfield', ['obj' => $gas, 'name' => 'mailserver', 'label' => 'Server SMTP'])
                    </div>
                    <div class="col-md-6">
                        @include('commons.numberfield', ['obj' => $gas, 'name' => 'mailport', 'label' => 'Porta'])
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        @include('commons.textfield', ['obj' => $gas, 'name' => 'mailaddress', 'label' => 'Indirizzo'])
                    </div>
                    <div class="col-md-6">
                        @include('commons.boolfield', ['obj' => $gas, 'name' => 'mailssl', 'label' => 'Abilita SSL'])
                    </div>
                </div>
            </div>

            <div class="well">
                <div class="row">
                    <div class="col-md-12">
                        @include('commons.textfield', ['obj' => $gas, 'name' => 'ridname', 'label' => 'Denominazione'])
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        @include('commons.textfield', ['obj' => $gas, 'name' => 'ridiban', 'label' => 'IBAN'])
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        @include('commons.textfield', ['obj' => $gas, 'name' => 'ridcode', 'label' => 'Codice Azienda'])
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="btn-group pull-right main-form-buttons" role="group" aria-label="Opzioni">
                <button type="submit" class="btn btn-success saving-button">Salva</button>
            </div>
        </div>
    </div>
</form>

@can('gas.permissions', $gas)
    <div class="page-header">
        <h3>Permessi</h3>
    </div>

    <div class="row">
        <div class="col-md-12">
            @include('commons.addingbutton', [
                'template' => 'permissions.base-edit',
                'typename' => 'role',
                'typename_readable' => 'Ruolo',
                'targeturl' => 'roles'
            ])
        </div>
    </div>

    <div class="clearfix"></div>
    <br/>

    <div class="row">
        <div class="col-md-12">
            @include('commons.loadablelist', ['identifier' => 'role-list', 'items' => App\Role::orderBy('name', 'asc')->get()])
        </div>
    </div>

    <br/>
@endcan

@endsection
