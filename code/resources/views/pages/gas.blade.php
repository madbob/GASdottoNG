@extends($theme_layout)

@section('content')

<div class="row">
</div>

<form class="form-horizontal inner-form gas-editor" method="PUT" action="{{ url('gas/' . $gas->id) }}">
    <div class="row">
        <div class="col-md-6">
            <div class="well">
                <div class="page-header">
                    <h3>Configurazioni Generali</h3>
                </div>

                @include('commons.textfield', ['obj' => $gas, 'name' => 'name', 'label' => 'Nome', 'mandatory' => true])
                @include('commons.textfield', ['obj' => $gas, 'name' => 'email', 'label' => 'E-Mail', 'mandatory' => true])
                @include('commons.imagefield', ['obj' => $gas, 'name' => 'logo', 'label' => 'Logo Homepage', 'valuefrom' => 'logo_url'])
                @include('commons.textarea', ['obj' => $gas, 'name' => 'message', 'label' => 'Messaggio Homepage'])

                @if(App\Role::someone('gas.access', $gas))
                    @include('commons.boolfield', ['obj' => $gas, 'name' => 'restricted', 'label' => 'Modalità Manutenzione'])
                @endif

                <div class="form-group">
                    <label class="col-sm-{{ $labelsize }} control-label">Importazione</label>
                    <div class="col-sm-{{ $fieldsize }}">
                        <button type="button" class="btn btn-default" data-toggle="modal" data-target="#importGDXP">Importa GDXP</button>
                        @push('postponed')
                            <div class="modal fade wizard" id="importGDXP" tabindex="-1" role="dialog">
                                <div class="modal-dialog modal-lg" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                            <h4 class="modal-title">Importa GDXP</h4>
                                        </div>
                                        <div class="wizard_page">
                                            <form class="form-horizontal" method="POST" action="{{ url('import/gdxp?step=read') }}" data-toggle="validator" enctype="multipart/form-data">
                                                <div class="modal-body">
                                                    <p>
                                                        GDXP è un formato interoperabile per scambiare listini e ordini tra diversi gestionali. Da qui puoi importare un file in tale formato.
                                                    </p>

                                                    <hr/>

                                                    @include('commons.filefield', [
                                                        'obj' => null,
                                                        'name' => 'file',
                                                        'label' => 'File da Caricare',
                                                        'mandatory' => true,
                                                        'extra_class' => 'immediate-run',
                                                        'extras' => [
                                                            'data-url' => url('import/gdxp?step=read'),
                                                            'data-run-callback' => 'wizardLoadPage'
                                                        ]
                                                    ])
                                                </div>

                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
                                                    <button type="submit" class="btn btn-success">Avanti</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endpush
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="well">
                <div class="page-header">
                    <h3>Invio Mail</h3>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        @include('commons.textfield', ['obj' => $gas, 'name' => 'mailaddress', 'label' => 'Indirizzo'])
                        @include('commons.textfield', ['obj' => $gas, 'name' => 'mailusername', 'label' => 'Username'])
                        @include('commons.passwordfield', ['obj' => $gas, 'name' => 'mailpassword', 'label' => 'Password'])
                    </div>
                    <div class="col-md-6">
                        @include('commons.textfield', ['obj' => $gas, 'name' => 'mailserver', 'label' => 'Server SMTP'])
                        @include('commons.numberfield', ['obj' => $gas, 'name' => 'mailport', 'label' => 'Porta'])
                        @include('commons.selectenumfield', [
                            'obj' => $gas,
                            'name' => 'mailssl',
                            'label' => 'Crittografia',
                            'values' => [
                                [
                                    'label' => 'Nessuna',
                                    'value' => ''
                                ],
                                [
                                    'label' => 'SSL',
                                    'value' => 'ssl'
                                ],
                                [
                                    'label' => 'STARTTLS',
                                    'value' => 'tls'
                                ],
                            ]
                        ])
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="well">
                <div class="page-header">
                    <h3>RID/SEPA Bancari</h3>
                </div>

                @include('commons.textfield', ['obj' => $gas, 'name' => 'ridname', 'label' => 'Denominazione'])
                @include('commons.textfield', ['obj' => $gas, 'name' => 'ridiban', 'label' => 'IBAN'])
                @include('commons.textfield', ['obj' => $gas, 'name' => 'ridcode', 'label' => 'Codice Azienda'])
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

<div class="row">
    <div class="col-md-6">
        <div class="page-header">
            <h3>Luoghi di Consegna</h3>
        </div>

        <div class="row">
            <div class="col-md-12">
                @include('commons.addingbutton', [
                    'template' => 'deliveries.base-edit',
                    'typename' => 'delivery',
                    'typename_readable' => 'Luogo di Consegna',
                    'targeturl' => 'deliveries'
                ])
            </div>
        </div>

        <div class="clearfix"></div>
        <br/>

        <div class="row">
            <div class="col-md-12">
                @include('commons.loadablelist', [
                    'identifier' => 'delivery-list',
                    'items' => App\Delivery::orderBy('name', 'asc')->get(),
                    'empty_message' => 'Non ci sono elementi da visualizzare.<br/>Aggiungendo elementi, verrà attivata la possibilità per ogni utente di selezionare il proprio luogo di consegna preferito.'
                ])
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="page-header">
            <h3>Files Condivisi</h3>
        </div>

        <div class="row">
            <div class="col-md-12">
                @include('commons.addingbutton', [
                    'template' => 'attachment.base-edit',
                    'typename' => 'attachment',
                    'target_update' => 'attachment-list-' . $gas->id,
                    'typename_readable' => 'File',
                    'targeturl' => 'attachments',
                    'extra' => [
                        'target_type' => 'App\Gas',
                        'target_id' => $gas->id
                    ]
                ])
            </div>
        </div>

        <div class="clearfix"></div>
        <br/>

        <div class="row">
            <div class="col-md-12">
                @include('commons.loadablelist', [
                    'identifier' => 'attachment-list-' . $gas->id,
                    'items' => $gas->attachments
                ])
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="page-header">
            <h3>Aliquote IVA</h3>
        </div>

        <div class="row">
            <div class="col-md-12">
                @include('commons.addingbutton', [
                    'template' => 'vatrates.base-edit',
                    'typename' => 'vatrate',
                    'typename_readable' => 'Aliquota IVA',
                    'targeturl' => 'vatrates'
                ])
            </div>
        </div>

        <div class="clearfix"></div>
        <br/>

        <div class="row">
            <div class="col-md-12">
                @include('commons.loadablelist', [
                    'identifier' => 'vatrate-list',
                    'items' => App\VatRate::orderBy('name', 'asc')->get()
                ])
            </div>
        </div>
    </div>
</div>

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
            @include('commons.loadablelist', [
                'identifier' => 'role-list',
                'items' => App\Role::sortedByHierarchy()
            ])
        </div>
    </div>

    <br/>
@endcan

@stack('postponed')

@endsection
