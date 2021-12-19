<x-larastrap::accordionitem :label="_i('Contabilità')">
    <x-larastrap::form :obj="$gas" classes="inner-form gas-editor" method="PUT" :action="route('gas.update', $gas->id)">
        <div class="row">
            <input type="hidden" name="group" value="banking">

            <div class="col">
                <x-larastrap::text name="year_closing" :label="_i('Inizio Anno Sociale')" classes="date-to-month" :value="ucwords(strftime('%d %B', strtotime($gas->getConfig('year_closing'))))" textappend="<i class='bi-calendar'></i>" :pophelp="_i('In questa data le quote di iscrizione verranno automaticamente fatte scadere e dovranno essere rinnovate')" />
                <x-larastrap::price name="annual_fee_amount" :label="_i('Quota Annuale')" :pophelp="_i('Se non configurato (valore = 0) non verranno gestite le quote di iscrizione')" />
                <x-larastrap::price name="deposit_amount" :label="_i('Cauzione')" :pophelp="_i('Se non configurato (valore = 0) non verranno gestite le cauzioni da parte dei nuovi soci')" />
                <x-larastrap::check name="auto_fee" :label="_i('Addebita automaticamente quota alla scadenza dell\'anno sociale')" :pophelp="_i('Abilitando questa opzione, alla scadenza dell\'anno sociale saranno automaticamente aggiornate le quote di tutti i soci attivi, addebitandole direttamente nel credito utente.')" />

                <x-larastrap::check name="enable_rid" :label="_i('Abilita SEPA')" classes="collapse_trigger" :value="$gas->hasFeature('rid')" :pophelp="_i('Abilitando questa opzione e popolando i relativi campi verrà attivata l\'esportazione dei files SEPA, con cui automatizzare le transazioni bancarie. I files saranno generabili da Contabilità -> Stato Crediti -> Esporta SEPA. Dopo aver compilato questo form, per ogni utente dovrai specificare alcuni parametri dai relativi pannelli in Utenti')" />
                <div class="collapse" data-triggerable="enable_rid">
                    <div class="card">
                        <div class="card-body">
                            <x-larastrap::text name="rid->iban" :label="_i('IBAN')" :value="$gas->rid['iban'] ?? ''" />
                            <x-larastrap::text name="rid->id" :label="_i('Identificativo Creditore')" :value="$gas->rid['id'] ?? ''" />
                            <x-larastrap::text name="rid->org" :label="_i('Codice Univoco Azienda')" :value="$gas->rid['org'] ?? ''" />
                        </div>
                    </div>
                </div>

                <x-larastrap::check name="enable_paypal" :label="_i('Abilita PayPal')" classes="collapse_trigger" :value="$gas->hasFeature('paypal')" :pophelp="_i('Abilitando questa opzione e popolando i relativi campi verranno attivati i pagamenti con PayPal, con cui gli utenti potranno autonomamente ricaricare il proprio credito direttamente da GASdotto. Per ottenere le credenziali visita https://developer.paypal.com/')" />
                <div class="collapse" data-triggerable="enable_paypal">
                    <div class="card">
                        <div class="card-body">
                            <x-larastrap::text name="paypal->client_id" :label="_i('Client ID')" :value="$gas->paypal['client_id']" />
                            <x-larastrap::text name="paypal->secret" :label="_i('Secret')" :value="$gas->paypal['secret']" />
                            <x-larastrap::radios name="paypal->mode" :label="_i('Modalità')" :options="['sandbox' => _i('Sandbox (per testing)'), 'live' => _i('Live')]" :value="$gas->paypal['mode']" />
                        </div>
                    </div>
                </div>

                <x-larastrap::check name="enable_satispay" :label="_i('Abilita Satispay')" classes="collapse_trigger" :value="$gas->hasFeature('satispay')" :pophelp="_i('Abilitando questa opzione e popolando i relativi campi verranno attivati i pagamenti con Satispay, con cui gli utenti potranno autonomamente ricaricare il proprio credito direttamente da GASdotto. Per ottenere le credenziali visita https://business.satispay.com/')" />
                <div class="collapse" data-triggerable="enable_satispay">
                    <div class="card">
                        <div class="card-body">
                            <x-larastrap::text name="satispay->secret" :label="_i('Security Bearer')" :value="$gas->satispay['secret']" />
                        </div>
                    </div>
                </div>

                <x-larastrap::check name="enable_integralces" :label="_i('Abilita IntegralCES')" classes="collapse_trigger" :value="$gas->hasFeature('integralces')" :pophelp="_i('Abilitando questa opzione sarà possibile gestire la contabilità (saldi, pagamenti, movimenti...) con una moneta complementare, ed accedere ad alcune funzioni di integrazione con IntegralCES')" />
                <div class="collapse" data-triggerable="enable_integralces">
                    <div class="card">
                        <div class="card-body">
                            <x-larastrap::text name="integralces->symbol" :label="_i('Valuta')" :value="$gas->integralces['symbol']" />
                            <x-larastrap::text name="integralces->identifier" :label="_i('Identificativo conto del GAS')" :value="$gas->integralces['identifier']" />
                        </div>
                    </div>
                </div>

                <x-larastrap::check name="enable_extra_invoicing" :label="_i('Abilita Emissione Fatture')" classes="collapse_trigger" :value="$gas->hasFeature('extra_invoicing')" :pophelp="_i('Abilitando questa opzione e popolando i relativi campi verrà attivata l\'emissione delle fatture nei confronti degli utenti che effettuano prenotazioni. Le fatture saranno emesse al momento del salvataggio o della consegna della prenotazione, e saranno accessibili da Contabilità -> Fatture')" />
                <div class="collapse" data-triggerable="enable_extra_invoicing">
                    <div class="card">
                        <div class="card-body">
                            <x-larastrap::text name="extra_invoicing->business_name" :label="_i('Ragione Sociale')" :value="$gas->extra_invoicing['business_name']" />
                            <x-larastrap::text name="extra_invoicing->taxcode" :label="_i('Codice Fiscale')" :value="$gas->extra_invoicing['taxcode']" classes="required_when_triggered" data-alternative-required="extra_invoicing->vat" />
                            <x-larastrap::text name="extra_invoicing->vat" :label="_i('Partita IVA')" :value="$gas->extra_invoicing['vat']" classes="required_when_triggered" data-alternative-required="extra_invoicing->taxcode" />
                            <x-larastrap::text name="extra_invoicing->address" :label="_i('Indirizzo')" :value="$gas->extra_invoicing['address']" />
                            <x-larastrap::number name="extra_invoicing->invoices_counter" :label="_i('Contatore Fatture')" :value="$gas->extra_invoicing['invoices_counter']" :pophelp="_i('Modifica questo parametro con cautela!')" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-larastrap::form>
</x-larastrap::accordionitem>
