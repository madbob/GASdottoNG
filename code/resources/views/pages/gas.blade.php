@extends('app')

@section('content')

<div class="row">
    <div class="col-md-6">
        <div class="panel-group" id="main-configs" role="tablist" aria-multiselectable="true">
            <div class="panel panel-default">
                <div class="panel-heading" role="tab">
                    <h4 class="panel-title">
                        <a role="button" data-toggle="collapse" data-parent="#main-configs" href="#general-config">
                            {{ _i('Configurazioni Generali') }}
                        </a>
                    </h4>
                </div>
                <div id="general-config" class="panel-collapse collapse in" role="tabpanel">
                    <div class="panel-body">
                        <div class="row">
                            <form class="form-horizontal inner-form gas-editor" method="PUT" action="{{ route('gas.update', $gas->id) }}">
                                <input type="hidden" name="reload-whole-page" value="1">
                                <input type="hidden" name="group" value="general">

                                <div class="col-md-12">
                                    @include('commons.textfield', ['obj' => $gas, 'name' => 'name', 'label' => _i('Nome'), 'mandatory' => true])
                                    @include('commons.emailfield', ['obj' => $gas, 'name' => 'email', 'label' => _i('E-Mail'), 'mandatory' => true])
                                    @include('commons.imagefield', ['obj' => $gas, 'name' => 'logo', 'label' => _i('Logo Homepage'), 'valuefrom' => 'logo_url'])
                                    @include('commons.textarea', ['obj' => $gas, 'name' => 'message', 'label' => _i('Messaggio Homepage')])
                                    @include('commons.selectenumfield', ['obj' => $gas, 'name' => 'language', 'label' => _i('Lingua'), 'values' => getLanguages()])
                                    @include('commons.textfield', ['obj' => $gas, 'name' => 'currency', 'label' => _i('Valuta')])

                                    @if(App\Role::someone('gas.access', $gas))
                                        @include('commons.boolfield', ['obj' => $gas, 'name' => 'restricted', 'label' => _i('Modalità Manutenzione')])
                                    @endif

                                    <div class="btn-group pull-right main-form-buttons" role="group">
                                        <button type="submit" class="btn btn-success saving-button">{{ _i('Salva') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading" role="tab">
                    <h4 class="panel-title">
                        <a class="collapsed" role="button" data-toggle="collapse" data-parent="#main-configs" href="#orders-config">
                            {{ _i('Ordini e Consegne') }}
                        </a>
                    </h4>
                </div>
                <div id="orders-config" class="panel-collapse collapse" role="tabpanel">
                    <div class="panel-body">
                        <div class="row">
                            <form class="form-horizontal inner-form gas-editor" method="PUT" action="{{ route('gas.update', $gas->id) }}">
                                <input type="hidden" name="group" value="orders">

                                <div class="col-md-12">
                                    @include('commons.boolfield', ['obj' => $gas, 'name' => 'fast_shipping_enabled', 'label' => _i('Abilita Consegne Rapide')])

                                    <div class="btn-group pull-right main-form-buttons" role="group">
                                        <button type="submit" class="btn btn-success saving-button">{{ _i('Salva') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading" role="tab">
                    <h4 class="panel-title">
                        <a class="collapsed" role="button" data-toggle="collapse" data-parent="#main-configs" href="#accounting-config">
                            {{ _i('Contabilità') }}
                        </a>
                    </h4>
                </div>
                <div id="accounting-config" class="panel-collapse collapse" role="tabpanel">
                    <div class="panel-body">
                        <div class="row">
                            <form class="form-horizontal inner-form gas-editor" method="PUT" action="{{ route('gas.update', $gas->id) }}">
                                <input type="hidden" name="group" value="banking">

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="year_closing" class="col-sm-{{ $labelsize }} control-label">{{ _i('Inizio Anno Sociale') }}</label>
                                        <div class="col-sm-{{ $fieldsize }}">
                                            <div class="input-group">
                                                <input type="text" class="date-to-month form-control" name="year_closing" value="{{ ucwords(strftime('%d %B', strtotime($currentgas->getConfig('year_closing')))) }}" required autocomplete="off">
                                                <div class="input-group-addon">
                                                    <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="annual_fee_amount" class="col-sm-{{ $labelsize }} control-label">{{ _i('Quota Annuale') }}</label>
                                        <div class="col-sm-{{ $fieldsize }}">
                                            <div class="input-group">
                                                <input type="text" class="form-control number" name="annual_fee_amount" value="{{ printablePrice($currentgas->getConfig('annual_fee_amount')) }}" autocomplete="off">
                                                <div class="input-group-addon">{{ $currentgas->currency }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="deposit_amount" class="col-sm-{{ $labelsize }} control-label">{{ _i('Cauzione') }}</label>
                                        <div class="col-sm-{{ $fieldsize }}">
                                            <div class="input-group">
                                                <input type="text" class="form-control number" name="deposit_amount" value="{{ printablePrice($currentgas->getConfig('deposit_amount')) }}" autocomplete="off">
                                                <div class="input-group-addon">{{ $currentgas->currency }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <p>
                                        {!! _i("Popolando questi campi verrà attivata l'esportazione dei files SEPA, con cui automatizzare le transazioni bancarie.<br>I files saranno generabili da <strong>Contabilità -> Stato Crediti -> Esporta RID</strong><br>Dopo aver compilato questo form, per ogni utente dovrai specificare alcuni parametri.") !!}
                                    </p>

                                    @include('commons.textfield', ['obj' => $gas, 'name' => 'rid->iban', 'label' => _i('IBAN')])
                                    @include('commons.textfield', ['obj' => $gas, 'name' => 'rid->id', 'label' => _i('Identificativo Creditore')])
                                    @include('commons.textfield', ['obj' => $gas, 'name' => 'rid->org', 'label' => _i('Codice Univoco Azienda')])

                                    <div class="btn-group pull-right main-form-buttons" role="group">
                                        <button type="submit" class="btn btn-success saving-button">{{ _i('Salva') }}</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading" role="tab">
                    <h4 class="panel-title">
                        <a class="collapsed" role="button" data-toggle="collapse" data-parent="#main-configs" href="#import-config">
                            {{ _i('Importa') }}
                        </a>
                    </h4>
                </div>
                <div id="import-config" class="panel-collapse collapse" role="tabpanel">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="col-sm-{{ $labelsize }} control-label">{{ _i('Importazione') }}</label>
                                    <div class="col-sm-{{ $fieldsize }}">
                                        <button type="button" class="btn btn-default" data-toggle="modal" data-target="#importGDXP">{{ _i('Importa GDXP') }}</button>
                                        @push('postponed')
                                            <div class="modal fade wizard" id="importGDXP" tabindex="-1" role="dialog">
                                                <div class="modal-dialog modal-lg" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                            <h4 class="modal-title">{{ _i('Importa GDXP') }}</h4>
                                                        </div>
                                                        <div class="wizard_page">
                                                            <form class="form-horizontal" method="POST" action="{{ url('import/gdxp?step=read') }}" data-toggle="validator" enctype="multipart/form-data">
                                                                <div class="modal-body">
                                                                    <p>
                                                                        {{ _i("GDXP è un formato interoperabile per scambiare listini e ordini tra diversi gestionali. Da qui puoi importare un file in tale formato.") }}
                                                                    </p>

                                                                    <hr/>

                                                                    @include('commons.filefield', [
                                                                        'obj' => null,
                                                                        'name' => 'file',
                                                                        'label' => _i('File da Caricare'),
                                                                        'mandatory' => true,
                                                                        'extra_class' => 'immediate-run',
                                                                        'extras' => [
                                                                            'data-url' => url('import/gdxp?step=read'),
                                                                            'data-run-callback' => 'wizardLoadPage'
                                                                        ]
                                                                    ])
                                                                </div>

                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ _i('Annulla') }}</button>
                                                                    <button type="submit" class="btn btn-success">{{ _i('Avanti') }}</button>
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
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="panel-group" id="list-configs" role="tablist" aria-multiselectable="true">
            <div class="panel panel-default">
                <div class="panel-heading" role="tab">
                    <h4 class="panel-title">
                    <a class="collapsed" role="button" data-toggle="collapse" data-parent="#list-configs" href="#shippingplace-config">
                        {{ _i('Luoghi di Consegna') }}
                    </a>
                </div>
                <div id="shippingplace-config" class="panel-collapse collapse" role="tabpanel">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                @include('commons.addingbutton', [
                                    'template' => 'deliveries.base-edit',
                                    'typename' => 'delivery',
                                    'typename_readable' => _i('Luogo di Consegna'),
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
                                    'items' => $currentgas->deliveries,
                                    'empty_message' => _i('Non ci sono elementi da visualizzare.<br/>Aggiungendo elementi, verrà attivata la possibilità per ogni utente di selezionare il proprio luogo di consegna preferito.')
                                ])
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading" role="tab">
                    <h4 class="panel-title">
                    <a class="collapsed" role="button" data-toggle="collapse" data-parent="#list-configs" href="#files-config">
                        {{ _i('File Condivisi') }}
                    </a>
                </div>
                <div id="files-config" class="panel-collapse collapse" role="tabpanel">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                @include('commons.addingbutton', [
                                    'template' => 'attachment.base-edit',
                                    'typename' => 'attachment',
                                    'target_update' => 'attachment-list-' . $gas->id,
                                    'typename_readable' => _i('File'),
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
            </div>
            <div class="panel panel-default">
                <div class="panel-heading" role="tab">
                    <h4 class="panel-title">
                    <a class="collapsed" role="button" data-toggle="collapse" data-parent="#list-configs" href="#vat-config">
                        {{ _i('Aliquote IVA') }}
                    </a>
                </div>
                <div id="vat-config" class="panel-collapse collapse" role="tabpanel">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                @include('commons.addingbutton', [
                                    'template' => 'vatrates.base-edit',
                                    'typename' => 'vatrate',
                                    'typename_readable' => _i('Aliquota IVA'),
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
            </div>
        </div>
    </div>
</div>

@can('gas.permissions', $gas)
    @include('permissions.gas-management', ['gas' => $gas])
    <br/>
@endcan

@stack('postponed')

@endsection
