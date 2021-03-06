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

                                    @include('commons.textarea', [
                                        'obj' => $gas,
                                        'name' => 'message',
                                        'label' => _i('Messaggio Homepage'),
                                        'help_popover' => _i("Eventuale messaggio da visualizzare sulla pagina di autenticazione di GASdotto, utile per comunicazioni speciali verso i membri del GAS o come messaggio di benvenuto"),
                                    ])

                                    @include('commons.selectenumfield', [
                                        'obj' => $gas,
                                        'name' => 'language',
                                        'label' => _i('Lingua'),
                                        'values' => getLanguages()
                                    ])

                                    @include('commons.textfield', [
                                        'obj' => $gas,
                                        'name' => 'currency',
                                        'label' => _i('Valuta'),
                                        'help_popover' => _i("Simbolo della valuta in uso. Verrà usato in tutte le visualizzazioni in cui sono espressi dei prezzi"),
                                    ])

                                    @if(App\Role::someone('gas.access', $gas))
                                        @include('commons.boolfield', [
                                            'obj' => $gas,
                                            'name' => 'restricted',
                                            'label' => _i('Modalità Manutenzione'),
                                            'help_popover' => _i("Se abilitato, il login sarà inibito a tutti gli utenti che non hanno il permesso \"Accesso consentito anche in manutenzione\""),
                                        ])
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
                                        'default_checked' => $gas->hasFeature('public_registrations'),
                                        'help_popover' => _i("Quando questa opzione è abilitata, chiunque potrà registrarsi all'istanza per mezzo dell'apposito pannello (accessibile da quello di login). Gli amministratori addetti agli utenti riceveranno una mail di notifica per ogni nuovo utente registrato"),
                                    ])

                                    <div class="collapse {{ $gas->hasFeature('public_registrations') ? 'in' : '' }}" data-triggerable="enable_public_registrations">
                                        <div class="col-md-12">
                                            <div class="well">
                                                @include('commons.textfield', [
                                                    'obj' => $gas,
                                                    'name' => 'public_registrations->privacy_link',
                                                    'label' => _i('Link Privacy Policy'),
                                                ])
                                                @include('commons.textfield', [
                                                    'obj' => $gas,
                                                    'name' => 'public_registrations->terms_link',
                                                    'label' => _i("Link Condizioni d'Uso"),
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
                                    @include('commons.boolfield', [
                                        'obj' => $gas,
                                        'name' => 'restrict_booking_to_credit',
                                        'label' => _i('Permetti solo prenotazioni entro il credito disponibile')
                                    ])

                                    <?php

                                    $values_for_contacts = [];

                                    $values_for_contacts['none'] = (object) [
                                        'name' => _i('Nessuno'),
                                        'checked' => ($gas->booking_contacts == 'none'),
                                    ];

                                    $supplier_roles = App\Role::rolesByClass('App\Supplier');
                                    foreach($supplier_roles as $sr) {
                                        $values_for_contacts[$sr->id] = (object) [
                                            'name' => _i('Tutti %s', $sr->name),
                                            'checked' => ($gas->booking_contacts == $sr->id),
                                        ];
                                    }

                                    $values_for_contacts['manual'] = (object) [
                                        'name' => _i('Selezione manuale'),
                                        'checked' => ($gas->booking_contacts == 'manual'),
                                    ];

                                    ?>

                                    @include('commons.radios', [
                                        'name' => 'booking_contacts',
                                        'label' => _i('Visualizza contatti in prenotazioni'),
                                        'values' => $values_for_contacts,
                                    ])

                                    <div class="form-group">
                                        <?php $columns = $currentgas->orders_display_columns ?>
                                        <label for="order_columns" class="col-sm-{{ $labelsize }} control-label">
                                            @include('commons.helpbutton', ['help_popover' => _i("Colonne visualizzate di default nella griglia di riassunto degli ordini. È comunque sempre possibile modificare la visualizzazione dall'interno della griglia stessa per mezzo del selettore posto in alto a destra")])
                                            {{ _i('Colonne Riassunto Ordini') }}
                                        </label>

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
                                        <label for="year_closing" class="col-sm-{{ $labelsize }} control-label">
                                            @include('commons.helpbutton', ['help_popover' => _i("In questa data le quote di iscrizione verranno automaticamente fatte scadere e dovranno essere rinnovate")])
                                            {{ _i('Inizio Anno Sociale') }}
                                        </label>
                                        <div class="col-sm-{{ $fieldsize }}">
                                            <div class="input-group">
                                                <input type="text" class="date-to-month form-control" name="year_closing" value="{{ ucwords(strftime('%d %B', strtotime($currentgas->getConfig('year_closing')))) }}" required autocomplete="off">
                                                <div class="input-group-addon">
                                                    <span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    @include('commons.decimalfield', [
                                        'obj' => $gas,
                                        'name' => 'annual_fee_amount',
                                        'label' => _i('Quota Annuale'),
                                        'is_price' => true,
                                        'help_popover' => _i("Se non configurato (valore = 0) non verranno gestite le quote di iscrizione"),
                                    ])

                                    @include('commons.decimalfield', [
                                        'obj' => $gas,
                                        'name' => 'deposit_amount',
                                        'label' => _i('Cauzione'),
                                        'is_price' => true,
                                        'help_popover' => _i("Se non configurato (valore = 0) non verranno gestite le cauzioni da parte dei nuovi soci"),
                                    ])
                                </div>

                                @include('commons.boolfield', [
                                    'obj' => null,
                                    'name' => 'enable_rid',
                                    'label' => _i('Abilita SEPA'),
                                    'extra_class' => 'collapse_trigger',
                                    'default_checked' => $gas->hasFeature('rid'),
                                    'help_popover' => _i("Abilitando questa opzione e popolando i relativi campi verrà attivata l'esportazione dei files SEPA, con cui automatizzare le transazioni bancarie. I files saranno generabili da Contabilità -> Stato Crediti -> Esporta SEPA. Dopo aver compilato questo form, per ogni utente dovrai specificare alcuni parametri dai relativi pannelli in Utenti"),
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
                                    'default_checked' => $gas->hasFeature('paypal'),
                                    'help_popover' => _i("Abilitando questa opzione e popolando i relativi campi verranno attivati i pagamenti con PayPal, con cui gli utenti potranno autonomamente ricaricare il proprio credito direttamente da GASdotto. Per ottenere le credenziali visita https://developer.paypal.com/"),
                                ])

                                <div class="collapse {{ $gas->hasFeature('paypal') ? 'in' : '' }}" data-triggerable="enable_paypal">
                                    <div class="col-md-12">
                                        <div class="well">
                                            @include('commons.textfield', ['obj' => $gas, 'name' => 'paypal->client_id', 'label' => 'Client ID'])
                                            @include('commons.textfield', ['obj' => $gas, 'name' => 'paypal->secret', 'label' => 'Secret'])
                                            @include('commons.radios', [
                                                'name' => 'paypal->mode',
                                                'label' => _i('Modalità'),
                                                'values' => [
                                                    'sandbox' => (object) [
                                                        'name' => _i('Sandbox (per testing)'),
                                                        'checked' => ($gas->paypal['mode'] == 'sandbox')
                                                    ],
                                                    'live' => (object) [
                                                        'name' => _i('Live'),
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
                                    'default_checked' => $gas->hasFeature('satispay'),
                                    'help_popover' => _i("Abilitando questa opzione e popolando i relativi campi verranno attivati i pagamenti con Satispay, con cui gli utenti potranno autonomamente ricaricare il proprio credito direttamente da GASdotto. Per ottenere le credenziali visita https://business.satispay.com/"),
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
                                    'default_checked' => $gas->hasFeature('extra_invoicing'),
                                    'help_popover' => _i("Abilitando questa opzione e popolando i relativi campi verrà attivata l'emissione delle fatture nei confronti degli utenti che effettuano prenotazioni. Le fatture saranno emesse al momento del salvataggio o della consegna della prenotazione, e saranno accessibili da Contabilità -> Fatture"),
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
                            {{ _i('E-Mail') }}
                        </a>
                    </h4>
                </div>
                <div id="custom-mails-config" class="panel-collapse collapse" role="tabpanel">
                    <div class="panel-body">
                        <div class="row">
                            <form class="form-horizontal inner-form gas-editor" method="PUT" action="{{ route('gas.update', $gas->id) }}">
                                <input type="hidden" name="group" value="mails">

                                <div class="col-md-12">
                                    @include('commons.boolfield', ['obj' => $gas, 'name' => 'notify_all_new_orders', 'label' => _i("Invia notifica a tutti gli utenti all'apertura di un ordine")])
                                    @include('commons.boolfield', ['obj' => $gas, 'name' => 'auto_user_order_summary', 'label' => _i("Invia riepilogo automatico agli utenti che hanno partecipato ad un ordine, quando viene chiuso")])
                                    @include('commons.boolfield', ['obj' => $gas, 'name' => 'auto_supplier_order_summary', 'label' => _i("Invia riepilogo automatico al fornitore di un ordine, quando viene chiuso")])

                                    <hr>
                                </div>

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

                                        if (($metadata->enabled)($gas) == false) {
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
                            {{ _i('Importa/Esporta') }}
                        </a>
                    </h4>
                </div>
                <div id="import-config" class="panel-collapse collapse" role="tabpanel">
                    <div class="panel-body">
                        <div class="row">
                            @if(env('HUB_URL'))
                                <form class="form-horizontal inner-form gas-editor" method="PUT" action="{{ route('gas.update', $gas->id) }}">
                                    <input type="hidden" name="group" value="import">

                                    <div class="col-md-12">
                                        @include('commons.boolfield', [
                                            'obj' => $gas,
                                            'name' => 'es_integration',
                                            'label' => _i('Integrazione Hub Economia Solidale'),
                                            'help_text' => _i("Abilita alcune funzioni (sperimentali!) di integrazione con hub.economiasolidale.net, tra cui l'aggiornamento automatico dei listini e l'aggregazione degli ordini con altri GAS."),
                                        ])
                                    </div>

                                    <div class="col-md-12">
                                        <div class="btn-group pull-right main-form-buttons" role="group">
                                            <button type="submit" class="btn btn-success saving-button">{{ _i('Salva') }}</button>
                                        </div>
                                    </div>
                                </form>

                                <hr>
                            @endif

                            <div class="col-md-12 form-horizontal">
                                <div class="form-group">
                                    <label class="col-sm-{{ $labelsize }} control-label">
                                        @include('commons.helpbutton', ['help_popover' => _i("Da qui è possibile importare un file GDXP generato da un'altra istanza di GASdotto o da qualsiasi altra piattaforma che supporta il formato")])
                                        {{ _i('Importazione') }}
                                    </label>
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
                                <div class="form-group">
                                    <label class="col-sm-{{ $labelsize }} control-label">
                                        {{ _i('Esporta database') }}
                                    </label>
                                    <div class="col-sm-{{ $fieldsize }}">
                                        <a href="{{ route('gas.dumpdb') }}" class="btn btn-default">{{ _i('Download') }} <span class="glyphicon glyphicon-download" aria-hidden="true"></span></a>
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
                                    'empty_message' => _i('Non ci sono elementi da visualizzare.<br/>Aggiungendo elementi verrà attivata la possibilità per ogni utente di selezionare il proprio luogo di consegna preferito, nei documenti di riassunto degli ordini le prenotazioni saranno suddivise per luogo, e sarà possibile attivare alcuni ordini solo per gli utenti afferenti a determinati luoghi di consegna: utile per GAS distribuiti sul territorio.')
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

            @if(env('GASDOTTO_NET', false))
                <?php $logs = App\InnerLog::where('type', 'mail')->orderBy('created_at', 'desc')->take(10)->get() ?>
                <div class="panel panel-default">
                    <div class="panel-heading" role="tab">
                        <h4 class="panel-title">
                        <a class="collapsed" role="button" data-toggle="collapse" data-parent="#list-configs" href="#email-logs">
                            {{ _i('Log E-Mail') }}
                        </a>
                    </div>
                    <div id="email-logs" class="panel-collapse collapse" role="tabpanel">
                        <div class="panel-body">
                            @if($logs->isEmpty())
                                <div class="alert alert-info">
                                    {{ _i('Non ci sono log relativi alle email.') }}
                                </div>
                            @else
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th width="30%">{{ _i('Data') }}</th>
                                            <th width="70%">{{ _i('Messaggio') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($logs as $log)
                                            <tr>
                                                <td>{{ printableDate($log->created_at) }}</td>
                                                <td>{{ $log->message }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@can('gas.permissions', $gas)
    @include('permissions.gas-management', ['gas' => $gas])
    <br/>
@endcan

@stack('postponed')

@endsection
