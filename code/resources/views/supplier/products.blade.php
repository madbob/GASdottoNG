@can('supplier.modify', $supplier)
    <div class="row">
        <div class="col-md-12">
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

            <button class="btn btn-default export-custom-list" data-export-url="{{ url('suppliers/catalogue/' . $supplier->id . '/pdf') }}">{{ _i('Listino PDF') }}</button>
            <button class="btn btn-default export-custom-list" data-export-url="{{ url('suppliers/catalogue/' . $supplier->id . '/csv') }}">{{ _i('Listino CSV') }}</button>
        </div>
    </div>

    <div class="clearfix"></div>

    <div class="middle-tabs">
        <hr/>
        <ul class="nav nav-pills" role="tablist">
            <li role="presentation" class="active">
                <a role="tab" data-toggle="tab" href="#product-full-list-{{ $supplier->id }}" data-async-load="{{ url('suppliers/' . $supplier->id . '/products') }}">{{ _i('Dettagli') }}</a>
            </li>
            <li role="presentation">
                <a role="tab" data-toggle="tab" href="#product-rapid-list-{{ $supplier->id }}" data-async-load="{{ url('suppliers/' . $supplier->id . '/products_grid') }}">{{ _i('Modifica Rapida') }}</a>
            </li>
        </ul>
    </div>

    <div class="tab-content">
        <div role="tabpanel" class="tab-pane details-list active" id="product-full-list-{{ $supplier->id }}">
            @include('supplier.products_details', ['supplier' => $supplier])
        </div>
        <div role="tabpanel" class="tab-pane rapid-list" id="product-rapid-list-{{ $supplier->id }}">
        </div>
    </div>
@else
    @include('supplier.products_details', ['supplier' => $supplier])
@endcan
