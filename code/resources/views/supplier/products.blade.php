@can('supplier.modify', $supplier)
    @if($supplier->remote_lastimport)
        <x-larastrap::suggestion>
            {{ _i("Il listino di questo fornitore è stato importato dall'archivio centralizzato: si raccomanda si modificarlo il meno possibile in modo che sia più semplice poi gestirne gli aggiornamenti futuri.") }}
        </x-larastrap::suggestion>
    @endif

    <div class="row">
        <div class="col">
            @include('commons.addingbutton', [
                'template' => 'product.base-edit',
                'typename' => 'product',
                'target_update' => 'product-list-' . $supplier->id,
                'typename_readable' => _i('Prodotto'),
                'targeturl' => 'products',
                'extra' => [
                    'supplier_id' => $supplier->id
                ]
            ])

            @include('commons.importcsv', [
                'modal_id' => 'importCSV' . $supplier->id,
                'import_target' => 'products',
                'modal_extras' => [
                    'supplier_id' => $supplier->id
                ]
            ])

            <x-larastrap::mbutton :label="_i('Esporta Listino')" triggers_modal="#export_products" />
            <x-larastrap::modal :title="_i('Esporta Listino')" id="export_products" classes="close-on-submit">
                <x-larastrap::form method="GET" :action="route('suppliers.catalogue', ['id' => $supplier->id])" :buttons="[['label' => _i('Download'), 'type' => 'submit']]">
                    <p>
                        {!! _i("Per la consultazione e l'elaborazione dei files in formato CSV (<i>Comma-Separated Values</i>) si consiglia l'uso di <a target=\"_blank\" href=\"http://it.libreoffice.org/\">LibreOffice</a>.") !!}
                    </p>

                    <hr/>

                    <x-larastrap::structchecks name="fields" :label="_i('Colonne')" :options="App\Formatters\Product::formattableColumns()" />
                    <x-larastrap::radios name="format" :label="_i('Formato')" :options="['pdf' => _i('PDF'), 'csv' => _i('CSV'), 'gdxp' => _i('GDXP')]" value="pdf" />
                </x-larastrap::form>
            </x-larastrap::modal>
        </div>
    </div>

    @if($supplier->active_orders->count() != 0)
        <br>
        <div class="alert alert-danger">
            {{ _i("Attenzione: ci sono ordini non ancora consegnati ed archiviati per questo fornitore. Eventuali nuovi prodotti qui aggiunti o disabilitati dovranno essere abilitati o rimossi esplicitamente nell'ordine, se desiderato, agendo sulla tabella dei prodotti.") }}
        </div>
    @endif

    <hr>

    <x-larastrap::tabs>
        <x-larastrap::remotetabpane :label="_i('Dettagli')" active="true" :button_attributes="['data-tab-url' => url('suppliers/' . $supplier->id . '/products')]" icon="bi-zoom-in">
            @include('supplier.products_details', ['supplier' => $supplier])
        </x-larastrap::remotetabpane>

        <x-larastrap::remotetabpane :label="_i('Modifica Rapida')" :button_attributes="['data-tab-url' => url('suppliers/' . $supplier->id . '/products_grid')]" icon="bi-lightning">
        </x-larastrap::remotetabpane>
    </x-larastrap::tabs>
@else
    @include('supplier.products_details', ['supplier' => $supplier])
@endcan
