<x-larastrap::tabs :id="sprintf('supplier-' . sanitizeId($supplier->id))">
    <x-larastrap::tabpane active="true" :label="_i('Dettagli')" icon="bi-tags">
        <x-larastrap::mform :obj="$supplier" method="PUT" :action="route('suppliers.update', $supplier->id)" classes="supplier-editor" :nodelete="$supplier->orders()->count() > 0">
            <input type="hidden" name="id" value="{{ $supplier->id }}" />

            <div class="row">
                <div class="col-md-6">
                    @include('supplier.base-edit', ['supplier' => $supplier])
                    <hr>
                    @include('commons.contactswidget', ['obj' => $supplier])
                </div>
                <div class="col-md-6">
                    @include('commons.statusfield', ['target' => $supplier])

                    <hr>

                    <x-larastrap::check name="fast_shipping_enabled" :label="_i('Abilita Consegne Veloci')" :pophelp="_i('Quando questa opzione è abilitata, nel pannello degli ordini per questo fornitore viene attivata la tab Consegne Veloci (accanto a Consegne) che permette di marcare più prenotazioni come consegnate in un\'unica operazione')" />
                    <x-larastrap::radiolist name="notify_on_close_enabled" :label="_i('Invia notifica alla chiusura degli ordini')" :pophelp="_i('Abilitando questa opzione, alla chiusura di ciascun ordine per questo fornitore la piattaforma provevderà a inoltrare automaticamente la tipologia di documento scelta. Ricorda: il Dettaglio Consegne contiene le prenotazioni divise per utente, mentre Riassunto Prodotti contiene solo le quantità complessivamente prenotate per ciascun prodotto')" :options="['none' => _i('No'), 'shipping' => _i('Dettaglio Consegne'), 'summary' => _i('Riassunto Prodotti'), 'shipping_summary' => _i('Dettaglio Consegne + Riassunto Prodotti')]" />

                    @if($currentgas->unmanaged_shipping == '1')
                        <x-larastrap::check name="unmanaged_shipping_enabled" :label="_i('Abilita Consegne Senza Quantità')" :pophelp="_i('Quando questa opzione è abilitata, nel pannello delle consegne per questo fornitore viene attivato un campo per immettere direttamente il valore totale della consegna anziché le quantità di ogni prodotto consegnato. Se questo campo viene usato, tutte le quantità presenti nella prenotazione si assumono essere consegnate e viene tenuto traccia della differenza del valore teorico e di quello reale immesso a mano.')" />
                    @endif

                    @include('commons.modifications', [
                        'obj' => $supplier,
                        'suggestion' => _i('Questi valori saranno usati come default per tutti i nuovi ordini di questo fornitore, ma sarà comunque possibile modificarli per ciascun ordine. Solo i modificatori valorizzati con qualche valore, o esplicitamente marcati come "sempre attivi", risulteranno accessibili dai relativi ordini.')
                    ])

                    @include('commons.permissionseditor', ['object' => $supplier, 'master_permission' => 'supplier.modify', 'editable' => true])
                </div>
            </div>

            <hr/>
        </x-larastrap::mform>
    </x-larastrap::tabpane>

    <x-larastrap::tabpane :label="_i('Ordini')" icon="bi-list-task">
        @include('supplier.orders', ['supplier' => $supplier])
    </x-larastrap::tabpane>

    <x-larastrap::tabpane tlabel="products.list" icon="bi-cart">
        @include('supplier.products', ['supplier' => $supplier])
    </x-larastrap::tabpane>

    <x-larastrap::tabpane :label="_i('File e Immagini')" icon="bi-files">
        @include('supplier.files', ['supplier' => $supplier])
    </x-larastrap::tabpane>

    @if(Gate::check('movements.view', $currentgas) || Gate::check('movements.admin', $currentgas))
        <x-larastrap::tabpane :label="_i('Contabilità')" icon="bi-piggy-bank">
            @include('supplier.accounting', ['supplier' => $supplier])
        </x-larastrap::tabpane>
    @endif
</x-larastrap::tabs>
