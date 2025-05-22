@can('supplier.modify', $supplier)
    @if($supplier->remote_lastimport)
        <x-larastrap::suggestion>
            {{ __('supplier.help.import_products_notice') }}
        </x-larastrap::suggestion>
    @endif

    <div class="row">
        <div class="col">
            @include('commons.addingbutton', [
                'template' => 'product.base-edit',
                'typename' => 'product',
                'target_update' => 'product-list-' . $supplier->id,
                'typename_readable' => __('products.name'),
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

            <x-larastrap::mbutton tlabel="supplier.export_products" triggers_modal="#export_products" />
            <x-larastrap::modal id="export_products" classes="close-on-submit">
                <x-larastrap::form method="GET" :action="route('suppliers.catalogue', ['id' => $supplier->id])" :buttons="[['tlabel' => 'generic.download', 'type' => 'submit']]">
                    <p>{{ __('export.help_csv_libreoffice') }}</p>

                    <hr/>

                    <x-larastrap::structchecks name="fields" tlabel="export.data.columns" :options="App\Formatters\Product::formattableColumns()" />

                    <x-larastrap::radios name="format" tlabel="export.data.format" :options="[
                        'pdf' => __('export.data.formats.pdf'),
                        'csv' => __('export.data.formats.csv'),
                        'gdxp' => __('export.data.formats.gdxp'),
                    ]" value="pdf" />
                </x-larastrap::form>
            </x-larastrap::modal>
        </div>
    </div>

    @if($supplier->active_orders->count() != 0)
        <br>
        <div class="alert alert-danger">
            {{ __('supplier.help.handling_products') }}
        </div>
    @endif

    <hr>

    <x-larastrap::tabs>
        <x-larastrap::remotetabpane tlabel="generic.details" active="true" :button_attributes="['data-tab-url' => url('suppliers/' . $supplier->id . '/products')]" icon="bi-zoom-in">
            @include('supplier.products_details', ['supplier' => $supplier])
        </x-larastrap::remotetabpane>

        <x-larastrap::remotetabpane tlabel="generic.fast_modify" :button_attributes="['data-tab-url' => url('suppliers/' . $supplier->id . '/products_grid')]" icon="bi-lightning">
        </x-larastrap::remotetabpane>
    </x-larastrap::tabs>
@else
    @include('supplier.products_details', ['supplier' => $supplier])
@endcan
