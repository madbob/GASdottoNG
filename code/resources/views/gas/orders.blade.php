<x-larastrap::accordionitem :label="_i('Ordini e Consegne')">
    <x-larastrap::form :obj="$gas" classes="inner-form gas-editor" method="PUT" :action="route('gas.update', $gas->id)">
        <div class="row">
            <input type="hidden" name="group" value="orders">

            <div class="col">
                <x-larastrap::check name="manual_products_sorting" :label="_i('Permetti di riorganizzare manualmente l\'elenco dei prodotti')" :pophelp="_i('Abilitando questa opzione, nel pannello di Modifica Rapida dei prodotti dei fornitori sarà possibile forzare un ordinamento arbitrario')" />
                <x-larastrap::check name="restrict_booking_to_credit" :label="_i('Permetti solo prenotazioni entro il credito disponibile')" />
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
