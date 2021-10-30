<x-larastrap::tabs>
    <x-larastrap::tabpane active="true" :label="_i('Dettagli')">
        <x-larastrap::mform :obj="$supplier" method="PUT" :action="route('suppliers.update', $supplier->id)" classes="supplier-editor" :nodelete="$supplier->orders()->count() > 0">
            <input type="hidden" name="id" value="{{ $supplier->id }}" />

            <div class="row">
                <div class="col-md-6">
                    @include('supplier.base-edit', ['supplier' => $supplier])

                    <hr>

                    <x-larastrap::field label="">
                        <div class="form-text">
                            {{ _i('Questi valori saranno usati come default per tutti i nuovi ordini di questo fornitore, ma sarà comunque possibile modificarli per ciascun ordine. Solo i modificatori valorizzati con qualche valore, o esplicitamente marcati come "sempre attivi", risulteranno accessibili dai relativi ordini.') }}
                        </div>
                    </x-larastrap::field>

                    @include('commons.modifications', ['obj' => $supplier])

                    <hr>

                    @include('commons.contactswidget', ['obj' => $supplier])
                </div>
                <div class="col-md-6">
                    <x-larastrap::check name="fast_shipping_enabled" :label="_i('Abilita Consegne Veloci')" :pophelp="_i('Quando questa opzione è abilitata, nel pannello degli ordini per questo fornitore viene attivata la tab Consegne Veloci (accanto a Consegne) che permette di marcare più prenotazioni come consegnate in un\'unica operazione')" />
                    <x-larastrap::check name="unmanaged_shipping_enabled" :label="_i('Abilita Consegne Senza Quantità')" :pophelp="_i('Quando questa opzione è abilitata, nel pannello delle consegne per questo fornitore viene attivato un campo per immettere direttamente il valore totale della consegna anziché le quantità di ogni prodotto consegnato. Se questo campo viene usato, tutte le quantità presenti nella prenotazione si assumono essere consegnate e viene tenuto traccia della differenza del valore teorico e di quello reale immesso a mano. Attenzione: l\'uso di questa funzione non permetterà di ottenere delle statistiche precise sui prodotti consegnati, né una ripartizione equa dei modificatori basati sulle quantità e sui pesi dei prodotti consegnati.')" />

                    @include('commons.statusfield', ['target' => $supplier])
                    <hr>
                    @include('commons.permissionseditor', ['object' => $supplier, 'master_permission' => 'supplier.modify', 'editable' => true])
                </div>
            </div>
        </x-larastrap::form>
    </x-larastrap::tabpane>

    <x-larastrap::tabpane :label="_i('Ordini')">
        @include('supplier.orders', ['supplier' => $supplier])
    </x-larastrap::tabpane>

    <x-larastrap::tabpane :label="_i('Prodotti')">
        @include('supplier.products', ['supplier' => $supplier])
    </x-larastrap::tabpane>

    <x-larastrap::tabpane :label="_i('File e Immagini')">
        @include('supplier.files', ['supplier' => $supplier])
    </x-larastrap::tabpane>

    @if(Gate::check('movements.view', $currentgas) || Gate::check('movements.admin', $currentgas))
        <x-larastrap::tabpane :label="_i('Contabilità')">
            @include('supplier.accounting', ['supplier' => $supplier])
        </x-larastrap::tabpane>
    @endif
</x-larastrap::tabs>
