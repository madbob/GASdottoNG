@extends('app')

@section('content')

<div class="card mb-2">
    <div class="card-header">
        <h3>{{ _i('Configurazioni') }}</h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <x-larastrap::accordion always_open="true">
                    <x-larastrap::accordionitem :label="_i('Configurazioni Generali')">
                        <x-larastrap::form :obj="$gas" classes="inner-form gas-editor" method="PUT" :action="route('gas.update', $gas->id)">
                            <div class="row">
                                <input type="hidden" name="reload-whole-page" value="1">
                                <input type="hidden" name="group" value="general">

                                <div class="col">
                                    <x-larastrap::text name="name" :label="_i('Nome del GAS')" required maxlength="20" />
                                    <x-larastrap::email name="email" :label="_i('E-Mail di Riferimento')" required />
                                    @include('commons.imagefield', ['obj' => $gas, 'name' => 'logo', 'label' => _i('Logo Homepage'), 'valuefrom' => 'logo_url'])
                                    <x-larastrap::textarea name="message" :label="_i('Messaggio Homepage')" :pophelp="_i('Eventuale messaggio da visualizzare sulla pagina di autenticazione di GASdotto, utile per comunicazioni speciali verso i membri del GAS o come messaggio di benvenuto')" />
                                    <x-larastrap::select name="language" :label="_i('Lingua')" :options="getLanguages()" />
                                    <x-larastrap::text name="currency" :label="_i('Valuta')" :pophelp="_i('Simbolo della valuta in uso. Verrà usato in tutte le visualizzazioni in cui sono espressi dei prezzi')" />

                                    @if(someoneCan('gas.access', $gas))
                                        <x-larastrap::check name="restricted" :label="_i('Modalità Manutenzione')" :pophelp="_i('Se abilitato, il login sarà inibito a tutti gli utenti che non hanno il permesso Accesso consentito anche in manutenzione')" />
                                    @endif
                                </div>
                            </div>
                        </x-larastrap::form>
                    </x-larastrap::accordionitem>

                    <x-larastrap::accordionitem :label="_i('Utenti')">
                        <x-larastrap::form :obj="$gas" classes="inner-form gas-editor" method="PUT" :action="route('gas.update', $gas->id)">
                            <div class="row">
                                <input type="hidden" name="group" value="users">

                                <div class="col">
                                    <x-larastrap::check name="enable_public_registrations" :label="_i('Abilita Registrazione Pubblica')" classes="collapse_trigger" :value="$gas->hasFeature('public_registrations')" :pophelp="_i('Quando questa opzione è abilitata, chiunque potrà registrarsi all\'istanza per mezzo dell\'apposito pannello (accessibile da quello di login). Gli amministratori addetti agli utenti riceveranno una mail di notifica per ogni nuovo utente registrato')" />
                                    <div class="collapse" data-triggerable="enable_public_registrations">
                                        <div class="col">
                                            <x-larastrap::url name="public_registrations->privacy_link" :label="_i('Link Privacy Policy')" :value="$gas->public_registrations['privacy_link']" />
                                            <x-larastrap::url name="public_registrations->terms_link" :label="_i('Link Condizioni d\'Uso')" :value="$gas->public_registrations['terms_link']" />

                                            <?php

                                            $selectable_mandatory = [
                                                'firstname' => _i('Nome'),
                                                'lastname' => _i('Cognome'),
                                                'email' => _i('E-Mail'),
                                                'phone' => _i('Telefono'),
                                            ];

                                            foreach($selectable_mandatory as $identifier => $label) {
                                                if (in_array($identifier, $gas->public_registrations['mandatory_fields'])) {
                                                    $selected_mandatory[] = $identifier;
                                                }
                                            }

                                            ?>

                                            <x-larastrap::checks name="public_registrations->mandatory_fields" :label="_i('Campi Obbligatori')" :options="$selectable_mandatory" :value="$selected_mandatory" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </x-larastrap::form>
                    </x-larastrap::accordionitem>

                    <x-larastrap::accordionitem :label="_i('Ordini e Consegne')">
                        <x-larastrap::form :obj="$gas" classes="inner-form gas-editor" method="PUT" :action="route('gas.update', $gas->id)">
                            <div class="row">
                                <input type="hidden" name="group" value="orders">

                                <div class="col">
                                    <x-larastrap::check name="restrict_booking_to_credit" :label="_i('Permetti solo prenotazioni entro il credito disponibile')" />

                                    <?php

                                    $values_for_contacts = [
                                        'none' => _i('Nessuno'),
                                        'manual' => _i('Selezione manuale'),
                                    ];

                                    $supplier_roles = rolesByClass('App\Supplier');
                                    foreach($supplier_roles as $sr) {
                                        $values_for_contacts[$sr->id] = _i('Tutti %s', $sr->name);
                                    }

                                    ?>

                                    <x-larastrap::radios name="booking_contacts" :label="_i('Visualizza contatti in prenotazioni')" :options="$values_for_contacts" classes="btn-group-vertical" />

                                    <x-larastrap::field :label="_i('Colonne Riassunto Ordini')" :pophelp="_i('Colonne visualizzate di default nella griglia di riassunto degli ordini. È comunque sempre possibile modificare la visualizzazione dall\'interno della griglia stessa per mezzo del selettore posto in alto a destra')">
                                        <?php $columns = $currentgas->orders_display_columns ?>
                                        @foreach(App\Order::displayColumns() as $identifier => $metadata)
                                            <div class="form-check form-switch">
                                                <input type="checkbox" name="orders_display_columns[]" class="form-check-input" value="{{ $identifier }}" {{ in_array($identifier, $columns) ? 'checked' : '' }}> {{ $metadata->label }}
                                                <small> - {{ $metadata->help }}</small>
                                            </div>
                                        @endforeach
                                    </x-larastrap::field>
                                </div>
                            </div>
                        </x-larastrap::form>
                    </x-larastrap::accordionitem>

                    <x-larastrap::accordionitem :label="_i('Contabilità')">
                        <x-larastrap::form :obj="$gas" classes="inner-form gas-editor" method="PUT" :action="route('gas.update', $gas->id)">
                            <div class="row">
                                <input type="hidden" name="group" value="banking">

                                <div class="col">
                                    <x-larastrap::text name="year_closing" :label="_i('Inizio Anno Sociale')" classes="date-to-month" :value="ucwords(strftime('%d %B', strtotime($gas->getConfig('year_closing'))))" textappend="<i class='bi-calendar'></i>" :pophelp="_i('In questa data le quote di iscrizione verranno automaticamente fatte scadere e dovranno essere rinnovate')" />
                                    <x-larastrap::price name="annual_fee_amount" :label="_i('Quota Annuale')" :pophelp="_i('Se non configurato (valore = 0) non verranno gestite le quote di iscrizione')" />
                                    <x-larastrap::price name="deposit_amount" :label="_i('Cauzione')" :pophelp="_i('Se non configurato (valore = 0) non verranno gestite le cauzioni da parte dei nuovi soci')" />

                                    <x-larastrap::check name="enable_rid" :label="_i('Abilita SEPA')" classes="collapse_trigger" :value="$gas->hasFeature('rid')" :pophelp="_i('Abilitando questa opzione e popolando i relativi campi verrà attivata l\'esportazione dei files SEPA, con cui automatizzare le transazioni bancarie. I files saranno generabili da Contabilità -> Stato Crediti -> Esporta SEPA. Dopo aver compilato questo form, per ogni utente dovrai specificare alcuni parametri dai relativi pannelli in Utenti')" />
                                    <div class="collapse" data-triggerable="enable_rid">
                                        <div class="col-md-12">
                                            <div class="well">
                                                <x-larastrap::text name="rid->iban" :label="_i('IBAN')" :value="$gas->rid['iban'] ?? ''" />
                                                <x-larastrap::text name="rid->id" :label="_i('Identificativo Creditore')" :value="$gas->rid['id'] ?? ''" />
                                                <x-larastrap::text name="rid->org" :label="_i('Codice Univoco Azienda')" :value="$gas->rid['org'] ?? ''" />
                                            </div>
                                        </div>
                                    </div>

                                    <x-larastrap::check name="enable_paypal" :label="_i('Abilita PayPal')" classes="collapse_trigger" :value="$gas->hasFeature('paypal')" :pophelp="_i('Abilitando questa opzione e popolando i relativi campi verranno attivati i pagamenti con PayPal, con cui gli utenti potranno autonomamente ricaricare il proprio credito direttamente da GASdotto. Per ottenere le credenziali visita https://developer.paypal.com/')" />
                                    <div class="collapse" data-triggerable="enable_paypal">
                                        <div class="col-md-12">
                                            <div class="well">
                                                <x-larastrap::text name="paypal->client_id" :label="_i('Client ID')" :value="$gas->paypal['client_id']" />
                                                <x-larastrap::text name="paypal->secret" :label="_i('Secret')" :value="$gas->paypal['secret']" />
                                                <x-larastrap::radios name="paypal->mode" :label="_i('Modalità')" :options="['sandbox' => _i('Sandbox (per testing)'), 'live' => _i('Live')]" :value="$gas->paypal['mode']" />
                                            </div>
                                        </div>
                                    </div>

                                    <x-larastrap::check name="enable_satispay" :label="_i('Abilita Satispay')" classes="collapse_trigger" :value="$gas->hasFeature('satispay')" :pophelp="_i('Abilitando questa opzione e popolando i relativi campi verranno attivati i pagamenti con Satispay, con cui gli utenti potranno autonomamente ricaricare il proprio credito direttamente da GASdotto. Per ottenere le credenziali visita https://business.satispay.com/')" />
                                    <div class="collapse" data-triggerable="enable_satispay">
                                        <div class="col-md-12">
                                            <div class="well">
                                                <x-larastrap::text name="satispay->secret" :label="_i('Security Bearer')" :value="$gas->satispay['secret']" />
                                            </div>
                                        </div>
                                    </div>

                                    <x-larastrap::check name="enable_extra_invoicing" :label="_i('Abilita Emissione Fatture')" classes="collapse_trigger" :value="$gas->hasFeature('extra_invoicing')" :pophelp="_i('Abilitando questa opzione e popolando i relativi campi verrà attivata l\'emissione delle fatture nei confronti degli utenti che effettuano prenotazioni. Le fatture saranno emesse al momento del salvataggio o della consegna della prenotazione, e saranno accessibili da Contabilità -> Fatture')" />
                                    <div class="collapse" data-triggerable="enable_extra_invoicing">
                                        <div class="col-md-12">
                                            <x-larastrap::text name="extra_invoicing->business_name" :label="_i('Ragione Sociale')" :value="$gas->extra_invoicing['business_name']" />
                                            <x-larastrap::text name="extra_invoicing->taxcode" :label="_i('Codice Fiscale')" :value="$gas->extra_invoicing['taxcode']" classes="required_when_triggered" data-alternative-required="extra_invoicing->vat" />
                                            <x-larastrap::text name="extra_invoicing->vat" :label="_i('Partita IVA')" :value="$gas->extra_invoicing['vat']" classes="required_when_triggered" data-alternative-required="extra_invoicing->taxcode" />
                                            <x-larastrap::text name="extra_invoicing->address" :label="_i('Indirizzo')" :value="$gas->extra_invoicing['address']" />
                                            <x-larastrap::number name="extra_invoicing->invoices_counter" :label="_i('Contatore Fatture')" :value="$gas->extra_invoicing['invoices_counter']" :pophelp="_i('Modifica questo parametro con cautela!')" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </x-larastrap::form>
                    </x-larastrap::accordionitem>

                    <x-larastrap::accordionitem :label="_i('E-Mail')">
                        <x-larastrap::form :obj="$gas" classes="inner-form gas-editor" method="PUT" :action="route('gas.update', $gas->id)">
                            <div class="row">
                                <input type="hidden" name="group" value="mails">

                                <div class="col">
                                    <x-larastrap::check name="notify_all_new_orders" :label="_i('Invia notifica a tutti gli utenti all\'apertura di un ordine')" />
                                    <x-larastrap::check name="auto_user_order_summary" :label="_i('Invia riepilogo automatico agli utenti che hanno partecipato ad un ordine, quando viene chiuso')" />
                                    <x-larastrap::check name="auto_supplier_order_summary" :label="_i('Invia riepilogo automatico al fornitore di un ordine, quando viene chiuso')" />

                                    <hr>

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
                                                $mail_params[] = sprintf('%%[%s]: %s', $placeholder, $placeholder_description);
                                            }
                                            $mail_help = join('<br>', $mail_params);
                                        }

                                        $current_subject = $gas->getConfig('mail_' . $identifier . '_subject');
                                        $current_body = $gas->getConfig('mail_' . $identifier . '_body');

                                        ?>

                                        <p>
                                            {{ $metadata->description }}
                                        </p>

                                        <x-larastrap::text :name="'custom_mails_' . $identifier . '_subject'" :label="_i('Soggetto')" :value="$current_subject" />
                                        <x-larastrap::textarea :name="'custom_mails_' . $identifier . '_body'" :label="_i('Testo')" :value="$current_body" :help="$mail_help" />

                                        <hr>
                                    @endforeach
                                </div>
                            </div>
                        </x-larastrap::form>
                    </x-larastrap::accordionitem>

                    <x-larastrap::accordionitem :label="_i('Importa/Esporta')">
                        <div class="row">
                            <div class="col">
                                @if(env('HUB_URL'))
                                    <x-larastrap::form classes="inner-form gas-editor" method="PUT" :action="route('gas.update', $gas->id)">
                                        <input type="hidden" name="group" value="import">

                                        <div class="col">
                                            <x-larastrap::check name="es_integration" :label="_i('Integrazione Hub Economia Solidale')" :pophelp="_i('Abilita alcune funzioni (sperimentali!) di integrazione con hub.economiasolidale.net, tra cui l\'aggiornamento automatico dei listini e l\'aggregazione degli ordini con altri GAS.')" />
                                        </div>
                                    </x-larastrap::form>

                                    <hr>

                                @endif

                                <x-larastrap::field :label="_i('Importazione')" :pophelp="_i('Da qui è possibile importare un file GDXP generato da un\'altra istanza di GASdotto o da qualsiasi altra piattaforma che supporta il formato')">
                                    <x-larastrap::mbutton :label="_i('Importa GDXP')" triggers_modal="#importGDXP" />
                                    @push('postponed')
                                        <x-larastrap::modal :title="_i('Importa GDXP')" id="importGDXP" classes="wizard">
                                            <div class="wizard_page">
                                                <x-larastrap::form method="POST" :action="url('import/gdxp?step=read')">
                                                    <p>
                                                        {{ _i("GDXP è un formato interoperabile per scambiare listini e ordini tra diversi gestionali. Da qui puoi importare un file in tale formato.") }}
                                                    </p>

                                                    <hr/>

                                                    <x-larastrap::file name="file" :label="_i('File da Caricare')" classes="immediate-run" required :data-url="url('import/gdxp?step=read')" />
                                                </x-larastrap::form>
                                            </div>
                                        </x-larastrap::modal>
                                    @endpush
                                </x-larastrap::field>

                                <x-larastrap::field :label="_i('Esporta database')">
                                    <a href="{{ route('gas.dumpdb') }}" class="btn btn-light">{{ _i('Download') }} <i class="bi-download"></i></a>
                                </x-larastrap::field>
                            </div>
                        </div>
                    </x-larastrap::accordionitem>
                </x-larastrap::accordion>
            </div>

            <div class="col-md-6">
                <x-larastrap::accordion always_open="true">
                    <x-larastrap::accordionitem :label="_i('Luoghi di Consegna')">
                        <div class="row">
                            <div class="col">
                                @include('commons.addingbutton', [
                                    'template' => 'deliveries.base-edit',
                                    'typename' => 'delivery',
                                    'typename_readable' => _i('Luogo di Consegna'),
                                    'targeturl' => 'deliveries'
                                ])
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col">
                                @include('commons.loadablelist', [
                                    'identifier' => 'delivery-list',
                                    'items' => $currentgas->deliveries,
                                    'empty_message' => _i('Non ci sono elementi da visualizzare.<br/>Aggiungendo elementi verrà attivata la possibilità per ogni utente di selezionare il proprio luogo di consegna preferito, nei documenti di riassunto degli ordini le prenotazioni saranno suddivise per luogo, e sarà possibile attivare alcuni ordini solo per gli utenti afferenti a determinati luoghi di consegna: utile per GAS distribuiti sul territorio.')
                                ])
                            </div>
                        </div>
                    </x-larastrap::accordionitem>

                    <x-larastrap::accordionitem :label="_i('File Condivisi')">
                        <div class="row">
                            <div class="col">
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

                        <div class="row mt-2">
                            <div class="col">
                                @include('commons.loadablelist', [
                                    'identifier' => 'attachment-list-' . $gas->id,
                                    'items' => $gas->attachments,
                                    'empty_message' => _i('Non ci sono elementi da visualizzare.<br/>I files qui aggiunti saranno accessibili a tutti gli utenti dalla dashboard: utile per condividere documenti di interesse comune.')
                                ])
                            </div>
                        </div>
                    </x-larastrap::accordionitem>

                    <x-larastrap::accordionitem :label="_i('Aliquote IVA')">
                        <div class="row">
                            <div class="col">
                                @include('commons.addingbutton', [
                                    'template' => 'vatrates.base-edit',
                                    'typename' => 'vatrate',
                                    'typename_readable' => _i('Aliquota IVA'),
                                    'targeturl' => 'vatrates'
                                ])
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col">
                                @include('commons.loadablelist', [
                                    'identifier' => 'vatrate-list',
                                    'items' => App\VatRate::orderBy('name', 'asc')->get(),
                                    'empty_message' => _i("Non ci sono elementi da visualizzare.<br/>Le aliquote potranno essere assegnate ai diversi prodotti nei listini dei fornitori, e vengono usate per scorporare automaticamente l'IVA dai totali delle fatture caricate in <strong>Contabilità -> Fatture</strong>.")
                                ])
                            </div>
                        </div>
                    </x-larastrap::accordionitem>

                    <x-larastrap::accordionitem :label="_i('Modificatori')">
                        <div class="row">
                            <div class="col">
                                @include('commons.addingbutton', [
                                    'template' => 'modifiertype.base-edit',
                                    'typename' => 'modtype',
                                    'typename_readable' => _i('Modificatore'),
                                    'targeturl' => 'modtypes'
                                ])
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col">
                                @include('commons.loadablelist', [
                                    'identifier' => 'modtype-list',
                                    'items' => App\ModifierType::orderBy('name', 'asc')->get(),
                                ])
                            </div>
                        </div>
                    </x-larastrap::accordionitem>

                    @if(env('GASDOTTO_NET', false))
                        <x-larastrap::accordionitem :label="_i('Log E-Mail')">
                            <?php $logs = App\InnerLog::where('type', 'mail')->orderBy('created_at', 'desc')->take(10)->get() ?>

                            <div class="row">
                                <div class="col">
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
                        </x-larastrap::accordionitem>
                    @endif
                </x-larastrap::accordion>
            </div>
        </div>
    </div>
</div>

@can('gas.permissions', $gas)
    <div id="permissions-management" class="card gas-permission-editor" data-fetch-url="{{ route('roles.index') }}">
        @include('permissions.gas-management', ['gas' => $gas])
    </div>
@endcan

<br>

@stack('postponed')

@endsection
