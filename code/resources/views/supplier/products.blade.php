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

            <button class="btn btn-default" data-toggle="modal" data-target="#export_products">{{ _i('Esporta Listino') }} <span class="glyphicon glyphicon-download" aria-hidden="true"></span></button>
            <div class="modal fade close-on-submit" id="export_products" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-extra-lg" role="document">
                    <div class="modal-content">
                        <form class="form-horizontal" method="GET" data-toggle="validator" novalidate>
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title">{{ _i('Esporta Listino') }}</h4>
                            </div>
                            <div class="modal-body">
                                <p>
                                    {{ _i("Verranno esportati i prodotti attualmente filtrati nella lista principale.") }}
                                </p>
                                <p>
                                    {!! _i("Per la consultazione e l'elaborazione dei files in formato CSV (<i>Comma-Separated Values</i>) si consiglia l'uso di <a target=\"_blank\" href=\"http://it.libreoffice.org/\">LibreOffice</a>.") !!}
                                </p>

                                <hr/>

                                @include('commons.checkboxes', [
                                    'name' => 'fields',
                                    'label' => _i('Colonne'),
                                    'labelsize' => 2,
                                    'fieldsize' => 10,
                                    'values' => App\Product::formattableColumns()
                                ])

                                @include('commons.radios', [
                                    'name' => 'format',
                                    'label' => _i('Formato'),
                                    'labelsize' => 2,
                                    'fieldsize' => 10,
                                    'values' => [
                                        'pdf' => (object) [
                                            'name' => 'PDF',
                                            'checked' => true
                                        ],
                                        'csv' => (object) [
                                            'name' => 'CSV'
                                        ],
                                    ]
                                ])
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-default" data-dismiss="modal">{{ _i('Annulla') }}</button>
                                <button class="btn btn-success export-custom-list" data-export-url="{{ url('suppliers/catalogue/' . $supplier->id) }}">{{ _i('Download') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <a class="btn btn-default" href="{{ $supplier->exportableURL() }}">{{ _i('Listino GDXP') }} <span class="glyphicon glyphicon-download" aria-hidden="true"></span></a>
        </div>
    </div>

    <div class="clearfix"></div>

    @if($supplier->orders()->whereNotIn('status', ['shipped', 'archived'])->count() != 0)
        <br>
        <div class="alert alert-danger">
            {{ _i('Attenzione: ci sono ordini non ancora consegnati ed archiviati per questo fornitore, eventuali modifiche ai prodotti saranno applicate anche a tali ordini.') }}
        </div>
        <br>
        <div class="clearfix"></div>
    @endif

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
