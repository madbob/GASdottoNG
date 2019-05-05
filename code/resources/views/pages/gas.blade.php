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
                                    @include('commons.textfield', ['obj' => $gas, 'name' => 'name', 'label' => _i('Nome del GAS'), 'mandatory' => true, 'max_length' => 20])
                                    @include('commons.emailfield', ['obj' => $gas, 'name' => 'email', 'label' => _i('E-Mail di Riferimento'), 'mandatory' => true])
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
                        <a class="collapsed" role="button" data-toggle="collapse" data-parent="#main-configs" href="#users-config">
                            {{ _i('Utenti') }}
                        </a>
                    </h4>
                </div>
                <div id="users-config" class="panel-collapse collapse" role="tabpanel">
                    <div class="panel-body">
                        <div class="row">
                            <form class="form-horizontal inner-form gas-editor" method="PUT" action="{{ route('gas.update', $gas->id) }}">
                                <input type="hidden" name="group" value="users">

                                <div class="col-md-12">
                                    @include('commons.boolfield', [
                                        'obj' => null,
                                        'name' => 'enable_public_registrations',
                                        'label' => _i('Abilita Registrazione Pubblica'),
                                        'extra_class' => 'collapse_trigger',
                                        'default_checked' => $gas->hasFeature('public_registrations')
                                    ])

                                    <div class="collapse {{ $gas->hasFeature('public_registrations') ? 'in' : '' }}" data-triggerable="enable_public_registrations">
                                        <div class="col-md-12">
                                            <div class="well">
                                                @include('commons.textfield', [
                                                    'obj' => $gas,
                                                    'name' => 'public_registrations->privacy_link',
                                                    'label' => _i('Link Privacy Policy'),
                                                    'help_text' => env('GASDOTTO_NET', false) ? 'Se non specificato, viene usata la privacy policy di default su https://www.gasdotto.net/' : ''
                                                ])
                                                @include('commons.checkboxes', [
                                                    'name' => 'public_registrations->mandatory_fields',
                                                    'label' => _i('Campi Obbligatori'),
                                                    'values' => [
                                                        'firstname' => (object) [
                                                            'name' => _i('Nome'),
                                                            'checked' => (in_array('firstname', $gas->public_registrations['mandatory_fields']))
                                                        ],
                                                        'lastname' => (object) [
                                                            'name' => _i('Cognome'),
                                                            'checked' => (in_array('lastname', $gas->public_registrations['mandatory_fields']))
                                                        ],
                                                        'email' => (object) [
                                                            'name' => _i('E-Mail'),
                                                            'checked' => (in_array('email', $gas->public_registrations['mandatory_fields']))
                                                        ],
                                                        'phone' => (object) [
                                                            'name' => _i('Telefono'),
                                                            'checked' => (in_array('phone', $gas->public_registrations['mandatory_fields']))
                                                        ],
                                                    ]
                                                ])
                                            </div>
                                        </div>
                                    </div>

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
                                    @include('commons.boolfield', ['obj' => $gas, 'name' => 'pending_packages_enabled', 'label' => _i('Prenotazioni per Completare Confezioni')])

                                    <div class="form-group">
                                        <?php $columns = $currentgas->orders_display_columns ?>
                                        <label for="order_columns" class="col-sm-{{ $labelsize }} control-label">{{ _i('Colonne Riassunto Ordini') }}</label>

                                        <div class="col-sm-{{ $fieldsize }}">
                                            @foreach(App\Order::displayColumns() as $identifier => $metadata)
                                                <input type="checkbox" name="orders_display_columns[]" value="{{ $identifier }}" data-toggle="toggle" data-size="mini" {{ in_array($identifier, $columns) ? 'checked' : '' }}> {{ $metadata->label }}
                                                <span class="help-block">{{ $metadata->help }}</span>
                                            @endforeach
                                        </div>
                                    </div>

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

                                @include('commons.boolfield', [
                                    'obj' => null,
                                    'name' => 'enable_rid',
                                    'label' => _i('Abilita SEPA'),
                                    'extra_class' => 'collapse_trigger',
                                    'default_checked' => $gas->hasFeature('rid')
                                ])

                                <div class="collapse {{ $gas->hasFeature('rid') ? 'in' : '' }}" data-triggerable="enable_rid">
                                    <div class="col-md-12">
                                        <div class="well">
                                            @include('commons.textfield', ['obj' => $gas, 'name' => 'rid->iban', 'label' => _i('IBAN')])
                                            @include('commons.textfield', ['obj' => $gas, 'name' => 'rid->id', 'label' => _i('Identificativo Creditore')])
                                            @include('commons.textfield', ['obj' => $gas, 'name' => 'rid->org', 'label' => _i('Codice Univoco Azienda')])
                                        </div>
                                    </div>
                                </div>

                                @include('commons.boolfield', [
                                    'obj' => null,
                                    'name' => 'enable_paypal',
                                    'label' => _i('Abilita PayPal'),
                                    'extra_class' => 'collapse_trigger',
                                    'default_checked' => $gas->hasFeature('paypal')
                                ])

                                <div class="collapse {{ $gas->hasFeature('paypal') ? 'in' : '' }}" data-triggerable="enable_paypal">
                                    <div class="col-md-12">
                                        <div class="well">
                                            @include('commons.textfield', ['obj' => $gas, 'name' => 'paypal->client_id', 'label' => 'Client ID'])
                                            @include('commons.textfield', ['obj' => $gas, 'name' => 'paypal->secret', 'label' => 'Secret'])
                                            @include('commons.radios', [
                                                'name' => 'paypal->mode',
                                                'label' => 'Modalità',
                                                'values' => [
                                                    'sandbox' => (object) [
                                                        'name' => 'Sandbox (per testing)',
                                                        'checked' => ($gas->paypal['mode'] == 'sandbox')
                                                    ],
                                                    'live' => (object) [
                                                        'name' => 'Live',
                                                        'checked' => ($gas->paypal['mode'] == 'live')
                                                    ],
                                                ]
                                            ])
                                        </div>
                                    </div>
                                </div>

                                @include('commons.boolfield', [
                                    'obj' => null,
                                    'name' => 'enable_satispay',
                                    'label' => _i('Abilita Satispay'),
                                    'extra_class' => 'collapse_trigger',
                                    'default_checked' => $gas->hasFeature('satispay')
                                ])

                                <div class="collapse {{ $gas->hasFeature('satispay') ? 'in' : '' }}" data-triggerable="enable_satispay">
                                    <div class="col-md-12">
                                        <div class="well">
                                            @include('commons.textfield', ['obj' => $gas, 'name' => 'satispay->secret', 'label' => 'Security Bearer'])
                                        </div>
                                    </div>
                                </div>

                                @include('commons.boolfield', [
                                    'obj' => null,
                                    'name' => 'enable_extra_invoicing',
                                    'label' => _i('Abilita Emissione Fatture'),
                                    'extra_class' => 'collapse_trigger',
                                    'default_checked' => $gas->hasFeature('extra_invoicing')
                                ])

                                <div class="collapse {{ $gas->hasFeature('extra_invoicing') ? 'in' : '' }}" data-triggerable="enable_extra_invoicing">
                                    <div class="col-md-12">
                                        <div class="well">
                                            @include('commons.textfield', ['obj' => $gas, 'name' => 'extra_invoicing->business_name', 'label' => _i('Ragione Sociale')])
                                            @include('commons.textfield', ['obj' => $gas, 'name' => 'extra_invoicing->taxcode', 'label' => _i('Codice Fiscale')])
                                            @include('commons.textfield', ['obj' => $gas, 'name' => 'extra_invoicing->vat', 'label' => _i('Partita IVA')])
                                            @include('commons.textfield', ['obj' => $gas, 'name' => 'extra_invoicing->address', 'label' => _i('Indirizzo')])
                                            @include('commons.numberfield', ['obj' => $gas, 'name' => 'extra_invoicing->invoices_counter', 'label' => 'Contatore Fatture', 'help_text' => _i('Modifica questo parametro con cautela!')])
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12">
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
                        <a class="collapsed" role="button" data-toggle="collapse" data-parent="#main-configs" href="#custom-mails-config">
                            {{ _i('Testi Messaggi Mail') }}
                        </a>
                    </h4>
                </div>
                <div id="custom-mails-config" class="panel-collapse collapse" role="tabpanel">
                    <div class="panel-body">
                        <div class="row">
                            <form class="form-horizontal inner-form gas-editor" method="PUT" action="{{ route('gas.update', $gas->id) }}">
                                <input type="hidden" name="group" value="mails">

                                <div class="col-md-12">
                                    <p>
                                        {{ _i('Da qui puoi modificare i testi delle mail in uscita da GASdotto. Per ogni tipologia sono previsti dei placeholders, che saranno sostituiti con gli opportuni valori al momento della generazione: per aggiungerli nei testi, usare la sintassi %[nome_placeholder]') }}
                                    </p>
                                    <p>
                                        {{ _i('Placeholder globali, che possono essere usati in tutti i messaggi:') }}
                                    </p>
                                    <ul>
                                        <li>gas_name: {{ _i('Nome del GAS') }}</li>
                                    </ul>

                                    <hr>

                                    @foreach(App\Config::customMailTypes() as $identifier => $metadata)
                                        <?php

                                        if ($identifier == 'welcome' && $gas->hasFeature('public_registrations') == false) {
                                            continue;
                                        }
                                        if ($identifier == 'receipt' && $gas->hasFeature('extra_invoicing') == false) {
                                            continue;
                                        }

                                        $mail_help = '';
                                        if (isset($metadata->params)) {
                                            $mail_params = [];
                                            foreach($metadata->params as $placeholder => $placeholder_description) {
                                                $mail_params[] = sprintf('%s: %s', $placeholder, $placeholder_description);
                                            }
                                            $mail_help = join('<br>', $mail_params);
                                        }

                                        ?>

                                        <p>
                                            {{ $metadata->description }}
                                        </p>

                                        @include('commons.textfield', [
                                            'obj' => $gas,
                                            'name' => "custom_mails_${identifier}_subject",
                                            'default_value' => $gas->getConfig("mail_${identifier}_subject"),
                                            'label' => _i('Soggetto')
                                        ])
                                        @include('commons.textarea', [
                                            'obj' => $gas,
                                            'name' => "custom_mails_${identifier}_body",
                                            'default_value' => $gas->getConfig("mail_${identifier}_body"),
                                            'label' => _i('Testo'),
                                            'help_text' => $mail_help
                                        ])
                                        <hr>
                                    @endforeach

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
                            <div class="col-md-12 form-horizontal">
                                <div class="form-group">
                                    <label class="col-sm-{{ $labelsize }} control-label">{{ _i('Importazione') }}</label>
                                    <div class="col-sm-{{ $fieldsize }}">
                                        <button type="button" class="btn btn-default" data-toggle="modal" data-target="#importGDXP">{{ _i('Importa GDXP') }} <span class="glyphicon glyphicon-modal-window" aria-hidden="true"></span></button>
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
                                    'empty_message' => _i('Non ci sono elementi da visualizzare.<br/>Aggiungendo elementi, verrà attivata la possibilità per ogni utente di selezionare il proprio luogo di consegna preferito e nei documenti di riassunto degli ordini le prenotazioni saranno suddivise per luogo: utile per GAS distribuiti sul territorio.')
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
                                    'items' => $gas->attachments,
                                    'empty_message' => _i('Non ci sono elementi da visualizzare.<br/>I files qui aggiunti saranno accessibili a tutti gli utenti dalla dashboard: utile per condividere documenti di interesse comune.')
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
                                    'items' => App\VatRate::orderBy('name', 'asc')->get(),
                                    'empty_message' => _i("Non ci sono elementi da visualizzare.<br/>Le aliquote potranno essere assegnate ai diversi prodotti nei listini dei fornitori, e vengono usate per scorporare automaticamente l'IVA dai totali delle fatture caricate in <strong>Contabilità -> Fatture</strong>.")
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
