<x-larastrap::accordionitem :label="_i('Ordini e Consegne')">
    <x-larastrap::form :obj="$gas" classes="inner-form gas-editor" method="PUT" :action="route('gas.update', $gas->id)">
        <div class="row">
            <input type="hidden" name="group" value="orders">

            <div class="col">
                <x-larastrap::check name="enable_restrict_booking_to_credit" :label="_i('Permetti solo prenotazioni entro il credito disponibile')" triggers_collapse="enable_restrict_booking_to_credit" :value="$gas->hasFeature('restrict_booking_to_credit')" />
                <x-larastrap::collapse id="enable_restrict_booking_to_credit">
                    <x-larastrap::number name="restrict_booking_to_credit->limit" :label="_i('Limite di Credito')" :value="$gas->restrict_booking_to_credit['limit']" :pophelp="_i('Gli utenti non possono prenotare nuovi prodotti se il loro credito diventa inferiore a questa soglia')" :textappend="defaultCurrency()->symbol" />
                </x-larastrap::collapse>

                <x-larastrap::check name="unmanaged_shipping" :label="_i('Permetti consegne manuali senza quantità')" :pophelp="_i('Abilitando questa opzione, sarà possibile attivare per ogni fornitore la possibilità di effettuare le consegne specificando direttamente il valore totale della consegna anziché le quantità di ogni prodotto consegnato. Attenzione: l\'uso di questa funzione non permetterà di ottenere delle statistiche precise sui prodotti consegnati, né una ripartizione equa dei modificatori basati sulle quantità e sui pesi dei prodotti consegnati.')" />

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

                <x-larastrap::field :label="_i('Colonne Attive in Dettaglio Consegne')" :pophelp="_i('Attributi selezionati di default durante l\'esportazione del Dettaglio Consegne degli ordini')">
                    <?php list($options, $values) = flaxComplexOptions(App\Formatters\User::formattableColumns()) ?>
                    <x-larastrap::checks name="orders_shipping_user_columns" :options="$options" squeeze />

                    <?php list($options, $values) = flaxComplexOptions(App\Formatters\Order::formattableColumns('shipping')) ?>
                    <x-larastrap::checks name="orders_shipping_product_columns" :options="$options" squeeze classes="mt-3" />
                </x-larastrap::field>
            </div>
        </div>
    </x-larastrap::form>
</x-larastrap::accordionitem>
