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

                    $current_config = json_decode($gas->getConfig('mail_' . $identifier));
                    $current_subject = $current_config->subject;
                    $current_body = $current_config->body;

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
