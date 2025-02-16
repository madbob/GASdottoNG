<x-larastrap::accordionitem :label="_i('E-Mail')">
    <x-larastrap::form :obj="$gas" classes="inner-form gas-editor" method="PUT" :action="route('gas.update', $gas->id)">
        <div class="row">
            <input type="hidden" name="group" value="mails">

            <div class="col">
                <x-larastrap::suggestion>
                    {{ _i('Da questa tabella puoi attivare specifiche tipologie di notifiche mail legate agli ordini, da inviare a diversi destinatari in base allo stato di ciascun ordine.') }}
                </x-larastrap::suggestion>

                <div class="table-responsive">
                    <table class="table inline-cells">
                        <thead>
                            <tr>
                                <th scope="col" width="20%">&nbsp;</th>
                                <th scope="col" width="20%">{{ _i('Aperto') }}</th>
                                <th scope="col" width="40%">{{ _i('In Chiusura') }}</th>
                                <th scope="col" width="20%">{{ _i('Chiuso') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th>{{ _i('Utenti') }}</th>
                                <td>
                                    <x-larastrap::check name="notify_all_new_orders" squeeze />
                                    <x-larastrap::pophelp :text="_i('Se questa opzione non viene abilitata, gli utenti ricevono solo le notifiche email per gli ordini dei fornitori che hanno individualmente abilitato dal proprio pannello di configurazione personale. Se viene abilitata, tutti gli utenti ricevono una notifica email ogni volta che un ordine viene aperto')" />
                                </td>
                                <td>
                                    <x-larastrap::check name="enable_send_order_reminder" squeeze triggers_collapse="send_order_reminder" :value="$gas->hasFeature('send_order_reminder')" />
                                    <x-larastrap::collapse id="send_order_reminder" label_width="8" input_width="4">
                                        <x-larastrap::number name="send_order_reminder" :label="_i('Quanti giorni prima?')" />
                                    </x-larastrap::collapse>
                                </td>
                                <td>
                                    <x-larastrap::check name="auto_user_order_summary" squeeze />
                                    <x-larastrap::pophelp :text="_i('La notifica viene inviata solo agli utenti che hanno partecipato all\'ordine')" />
                                </td>
                            </tr>
                            <tr>
                                <th>{{ _i('Referenti') }}</th>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>
                                    <x-larastrap::check name="auto_referent_order_summary" squeeze />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <hr>

                <x-larastrap::suggestion>
                    <p>
                        {{ _i('Da qui puoi modificare i testi delle mail in uscita da GASdotto. Per ogni tipologia sono previsti dei placeholders, che saranno sostituiti con gli opportuni valori al momento della generazione: per aggiungerli nei testi, usare la sintassi %[nome_placeholder]') }}
                    </p>
                    <p>
                        {{ _i('Placeholder globali, che possono essere usati in tutti i messaggi:') }}
                    </p>
                    <ul>
                        <li>gas_name: {{ _i('Nome del GAS') }}</li>
                    </ul>
                </x-larastrap::suggestion>

                @foreach(systemParameters('MailTypes') as $identifier => $metadata)
                    <?php

                    if ($metadata->enabled($gas) == false) {
                        continue;
                    }

                    $mail_help = $metadata->formatParams();
                    $current_config = json_decode($gas->getConfig('mail_' . $identifier));
                    $current_subject = $current_config->subject;
                    $current_body = $current_config->body;

                    ?>

                    <p>
                        {{ $metadata->description() }}
                    </p>

                    <x-larastrap::text :name="'custom_mails_' . $identifier . '_subject'" :label="_i('Soggetto')" :value="$current_subject" />
                    <x-larastrap::textarea :name="'custom_mails_' . $identifier . '_body'" :label="_i('Testo')" :value="$current_body" :help="$mail_help" />

                    <hr>
                @endforeach
            </div>
        </div>
    </x-larastrap::form>
</x-larastrap::accordionitem>
