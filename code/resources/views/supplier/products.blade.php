@can('supplier.modify', $supplier)
    <div class="row">
        <div class="col-md-12">
            @include('commons.addingbutton', [
                'template' => 'product.base-edit',
                'typename' => 'product',
                'target_update' => 'product-list-' . $supplier->id,
                'typename_readable' => 'Prodotto',
                'targeturl' => 'products',
                'extra' => ['supplier_id' => $supplier->id]
            ])

            <button type="button" class="btn btn-default" data-toggle="modal" data-target="#importCSV{{ $supplier->id }}">Importa CSV</button>
            <div class="modal fade wizard" id="importCSV{{ $supplier->id }}" tabindex="-1" role="dialog" aria-labelledby="importCSV{{ $supplier->id }}">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">Importa CSV</h4>
                        </div>
                        <div class="wizard_page">
                            <form class="form-horizontal" method="POST" action="{{ url('import/csv?type=products&step=guess') }}" data-toggle="validator" enctype="multipart/form-data">
                                <input type="hidden" name="supplier_id" value="{{ $supplier->id }}" />
                                <div class="modal-body">
                                    <p>
                                        Sono ammessi solo files in formato CSV. Si raccomanda di formattare la propria tabella in modo omogeneo, senza usare celle unite, celle vuote, intestazioni: ogni riga deve contenere tutte le informazioni relative al prodotto. I prezzi vanno espressi senza includere il simbolo dell'euro.
                                    </p>
                                    <p>
                                        Una volta caricato il file sarà possibile specificare quale attributo rappresenta ogni colonna trovata nel documento.
                                    </p>
                                    <p class="text-center">
                                        <img src="{{ url('images/csv_explain.png') }}">
                                    </p>

                                    <hr/>

                                    @include('commons.filefield', [
                                        'obj' => null,
                                        'name' => 'file',
                                        'label' => 'File da Caricare',
                                        'mandatory' => true,
                                        'extra_class' => 'immediate-run',
                                        'extras' => [
                                            'data-url' => 'import/csv?type=products&step=guess',
                                            'data-form-data' => '{"supplier_id": "' . $supplier->id . '"}',
                                            'data-run-callback' => 'wizardLoadPage'
                                        ]
                                    ])
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
                                    <button type="submit" class="btn btn-success">Avanti</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="clearfix"></div>

    <div class="row middle-tabs">
        <hr />
        <div class="btn-group" role="group">
            <button class="btn btn-default active" role="tab" data-target="#product-full-list-{{ $supplier->id }}">Dettagli</button>
            <button class="btn btn-default" role="tab" data-target="#product-rapid-list-{{ $supplier->id }}">Modifica Rapida</button>
        </div>
    </div>

    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="product-full-list-{{ $supplier->id }}">
            @endcan

            <div class="row">
                <div class="col-md-12">
                    @include('commons.loadablelist', [
                        'identifier' => 'product-list-' . $supplier->id,
                        'items' => $supplier->all_products,
                        'legend' => (object)[
                            'class' => 'Product'
                        ],
                        'filters' => [
                            'archived' => (object)[
                                'icon' => 'inbox',
                                'label' => 'Archiviati',
                                'value' => false
                            ]
                        ]
                    ])
                </div>
            </div>

            @can('supplier.modify', $supplier)
        </div>

        <div role="tabpanel" class="tab-pane" id="product-rapid-list-{{ $supplier->id }}">
            <form class="inner-form" method="POST" action="{{ url('products/massiveupdate') }}">
                <input type="hidden" name="post-saved-function" value="reloadCurrentLoadable">

                <div class="row">
                    <div class="col-md-12">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Unità di Misura</th>
                                    <th>Prezzo Unitario</th>
                                    <th>Prezzo Trasporto</th>
                                    <th>Ordinabile</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $measures = App\Measure::orderBy('name', 'asc')->get() ?>
                                @foreach($supplier->products as $product)
                                    <tr>
                                        <td>
                                            @include('commons.hiddenfield', [
                                                'obj' => $product,
                                                'name' => 'id',
                                                'postfix' => '[]'
                                            ])

                                            @include('commons.textfield', [
                                                'obj' => $product,
                                                'prefix' => $product->id . '-',
                                                'name' => 'name',
                                                'label' => 'Nome',
                                                'squeeze' => true,
                                                'mandatory' => true
                                            ])
                                        </td>
                                        <td>
                                            @include('commons.selectobjfield', [
                                                'obj' => $product,
                                                'prefix' => $product->id . '-',
                                                'name' => 'measure_id',
                                                'objects' => $measures,
                                                'label' => 'Unità di Misura',
                                                'squeeze' => true
                                            ])
                                        </td>
                                        <td>
                                            @include('commons.decimalfield', [
                                                'obj' => $product,
                                                'prefix' => $product->id . '-',
                                                'name' => 'price',
                                                'label' => 'Prezzo Unitario',
                                                'squeeze' => true,
                                                'is_price' => true,
                                                'mandatory' => true
                                            ])
                                        </td>
                                        <td>
                                            @include('commons.decimalfield', [
                                                'obj' => $product,
                                                'prefix' => $product->id . '-',
                                                'name' => 'transport',
                                                'label' => 'Prezzo Trasporto',
                                                'squeeze' => true,
                                                'is_price' => true
                                            ])
                                        </td>
                                        <td>
                                            @include('commons.boolfield', [
                                                'obj' => $product,
                                                'prefix' => $product->id . '-',
                                                'name' => 'active',
                                                'label' => 'Ordinabile',
                                                'squeeze' => true
                                            ])
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="btn-group pull-right" role="group">
                            <button type="submit" class="btn btn-success">Salva</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endcan
