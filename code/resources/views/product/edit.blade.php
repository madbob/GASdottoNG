<form class="form-horizontal main-form product-editor" method="PUT" action="{{ route('products.update', $product->id) }}">
    <div class="row">
        <div class="col-md-6">
            @include('product.base-edit', ['product' => $product])
            @include('commons.textfield', ['obj' => $product, 'name' => 'supplier_code', 'label' => 'Codice Fornitore'])
            @include('commons.boolfield', ['obj' => $product, 'name' => 'active', 'label' => 'Ordinabile'])
        </div>
        <div class="col-md-6">
            @include('commons.decimalfield', ['obj' => $product, 'name' => 'portion_quantity', 'label' => 'Pezzatura', 'decimals' => 3])
            @include('commons.boolfield', ['obj' => $product, 'name' => 'variable', 'label' => 'Variabile'])
            @include('commons.decimalfield', ['obj' => $product, 'name' => 'package_size', 'label' => 'Confezione', 'decimals' => 3])
            @include('commons.decimalfield', ['obj' => $product, 'name' => 'multiple', 'label' => 'Multiplo', 'decimals' => 3])
            @include('commons.decimalfield', ['obj' => $product, 'name' => 'min_quantity', 'label' => 'Minimo', 'decimals' => 3])
            @include('commons.decimalfield', ['obj' => $product, 'name' => 'max_quantity', 'label' => 'Massimo Consigliato', 'decimals' => 3])
            @include('commons.decimalfield', ['obj' => $product, 'name' => 'max_available', 'label' => 'Disponibile', 'decimals' => 3])
            @include('product.variantseditor', ['product' => $product])
        </div>
    </div>

    @include('commons.formbuttons', [
        'left_buttons' => [
            (object) [
                'label' => 'Duplica',
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
                    <h4 class="modal-title">Crea/Modifica Variante</h4>
                </div>
                <div class="modal-body">
                    @include('commons.textfield', ['obj' => null, 'name' => 'name', 'label' => 'Nome', 'mandatory' => true])
                    @include('commons.boolfield', ['obj' => null, 'name' => 'has_offset', 'label' => 'Differenza Prezzo'])

                    <div class="form-group">
                        <label class="col-sm-{{ $labelsize }} control-label">Valori</label>

                        <div class="col-sm-{{ $fieldsize }} values_table">
                            @include('commons.manyrows', [
                                'contents' => null,
                                'columns' => [
                                    [
                                        'label' => 'Valore',
                                        'field' => 'value',
                                        'type' => 'text'
                                    ],
                                    [
                                        'label' => 'Differenza Prezzo',
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
                    <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-success">Salva</button>
                </div>
            </form>
        </div>
    </div>
</div>
