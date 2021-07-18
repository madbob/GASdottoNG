@can('supplier.modify', $supplier)
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
            <x-larastrap::modal :title="_i('Esporta Listino')" id="export_products">
                <x-larastrap::form classes="direct-submit" method="GET" :action="url('suppliers/catalogue/' . $supplier->id)">
                    <p>
                        {!! _i("Per la consultazione e l'elaborazione dei files in formato CSV (<i>Comma-Separated Values</i>) si consiglia l'uso di <a target=\"_blank\" href=\"http://it.libreoffice.org/\">LibreOffice</a>.") !!}
                    </p>

                    <hr/>

                    <?php list($options, $values) = flaxComplexOptions(App\Product::formattableColumns()) ?>
                    <x-larastrap::checks name="fields" :label="_i('Colonne')" :options="$options" :value="$values" />

                    <x-larastrap::radios name="format" :label="_i('Formato')" :options="['pdf' => _i('PDF'), 'csv' => _i('CSV')]" value="pdf" />
                </x-larastrap::form>
            </x-larastrap::modal>

            <a class="btn btn-light" href="{{ $supplier->exportableURL() }}">{{ _i('Listino GDXP') }} <i class="bi-download"></i></a>
        </div>
    </div>

    @if($supplier->orders()->whereNotIn('status', ['shipped', 'archived'])->count() != 0)
        <br>
        <div class="alert alert-danger">
            {{ _i("Attenzione: ci sono ordini non ancora consegnati ed archiviati per questo fornitore, eventuali modifiche ai prodotti saranno applicate anche a tali ordini. Eventuali nuovi prodotti aggiunti dovranno invece essere abilitati esplicitamente nell'ordine, se desiderato, agendo sulla tabella dei prodotti.") }}
        </div>
    @endif

    <hr>

    <x-larastrap::tabs>
        <x-larastrap::remotetabpane :label="_i('Dettagli')" active="true" :button_attributes="['data-tab-url' => url('suppliers/' . $supplier->id . '/products')]">
            @include('supplier.products_details', ['supplier' => $supplier])
        </x-larastrap::remotetabpane>

        <x-larastrap::remotetabpane :label="_i('Modifica Rapida')" :button_attributes="['data-tab-url' => url('suppliers/' . $supplier->id . '/products_grid')]">
        </x-larastrap::remotetabpane>
    </x-larastrap::tabs>
@else
    @include('supplier.products_details', ['supplier' => $supplier])
@endcan
