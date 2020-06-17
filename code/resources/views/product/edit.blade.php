<form class="form-horizontal main-form product-editor" method="PUT" action="{{ route('products.update', $product->id) }}">
    @include('product.editform', ['product' => $product])

    @include('commons.formbuttons', [
        'left_buttons' => [
            (object) [
                'label' => _i('Duplica'),
                'url' => route('products.duplicate', $product->id),
                'class' => 'async-modal'
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
                    @include('commons.boolfield', ['obj' => null, 'name' => 'has_offset', 'label' => _i('Differenza Prezzo/Peso')])

                    <div class="form-group">
                        <label class="col-sm-{{ $labelsize }} control-label">{{ _i('Valori') }}</label>

                        <?php

                        $columns = [
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
                            ],
                        ];

                        if ($product->measure->discrete) {
                            $columns[] = [
                                'label' => _i('Differenza Peso'),
                                'field' => 'weight_offset',
                                'type' => 'decimal',
                                'extra' => [
                                    'postlabel' => _i('Chili')
                                ]
                            ];
                        }

                        ?>

                        <div class="col-sm-{{ $fieldsize }} values_table">
                            @include('commons.manyrows', [
                                'contents' => null,
                                'columns' => $columns,
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
