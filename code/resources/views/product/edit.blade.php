<form class="form-horizontal main-form product-editor" method="PUT" action="{{ route('products.update', $product->id) }}">
    <div class="row">
        <div class="col-md-6">
            @include('product.base-edit', ['product' => $product])
            @include('commons.textfield', ['obj' => $product, 'name' => 'supplier_code', 'label' => _i('Codice Fornitore')])
            @include('commons.boolfield', ['obj' => $product, 'name' => 'active', 'label' => _i('Ordinabile')])
        </div>
        <div class="col-md-6">
            @include('commons.imagefield', ['obj' => $product, 'name' => 'picture', 'label' => _i('Foto'), 'valuefrom' => 'picture_url'])
            @include('commons.decimalfield', ['obj' => $product, 'name' => 'portion_quantity', 'label' => _i('Pezzatura'), 'decimals' => 3])
            @include('commons.boolfield', ['obj' => $product, 'name' => 'variable', 'label' => _i('Variabile')])
            @include('commons.decimalfield', ['obj' => $product, 'name' => 'package_size', 'label' => _i('Confezione'), 'decimals' => 3])
            @include('commons.decimalfield', ['obj' => $product, 'name' => 'multiple', 'label' => _i('Multiplo'), 'decimals' => 3])
            @include('commons.decimalfield', ['obj' => $product, 'name' => 'min_quantity', 'label' => _i('Minimo'), 'decimals' => 3])
            @include('commons.decimalfield', ['obj' => $product, 'name' => 'max_quantity', 'label' => _i('Massimo Consigliato'), 'decimals' => 3])
            @include('commons.decimalfield', ['obj' => $product, 'name' => 'max_available', 'label' => _i('Disponibile'), 'decimals' => 3])
            @include('product.variantseditor', ['product' => $product])
        </div>
    </div>

    @include('commons.formbuttons', [
        'left_buttons' => [
            (object) [
                'label' => _i('Duplica'),
                'url' => '',
                'class' => 'duplicate-product'
            ]
        ]
    ])
</form>

<div class="modal fade create-variant" tabindex="-1" role="dialog" aria-labelledby="createVariant">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form class="form-horizontal creating-variant-form" method="POST" action="{{ route('variants.store') }}" data-toggle="validator">
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="variant_id" value="">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{ _i('Crea/Modifica Variante') }}</h4>
                </div>
                <div class="modal-body">
                    @include('commons.textfield', ['obj' => null, 'name' => 'name', 'label' => _i('Nome'), 'mandatory' => true])
                    @include('commons.boolfield', ['obj' => null, 'name' => 'has_offset', 'label' => _i('Differenza Prezzo')])

                    <div class="form-group">
                        <label class="col-sm-{{ $labelsize }} control-label">{{ _i('Valori') }}</label>

                        <div class="col-sm-{{ $fieldsize }} values_table">
                            @include('commons.manyrows', [
                                'contents' => null,
                                'columns' => [
                                    [
                                        'label' => _i('Valore'),
                                        'field' => 'value',
                                        'type' => 'text'
                                    ],
                                    [
                                        'label' => _i('Differenza Prezzo'),
                                        'field' => 'price_offset',
                                        'type' => 'decimal',
                                        'extra' => [
                                            'is_price' => true
                                        ]
                                    ]
                                ]
                            ])
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ _i('Annulla') }}</button>
                    <button type="submit" class="btn btn-success">{{ _i('Salva') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
