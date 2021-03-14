<div class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form class="form-horizontal inner-form" method="POST" action="{{ route('variants.store') }}" data-toggle="validator">
                <input type="hidden" name="reload-portion" value="#variants_editor_{{ sanitizeId($product->id) }}">
                <input type="hidden" name="close-modal" value="1">

                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="variant_id" value="{{ $variant ? $variant->id : '' }}">

                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{ _i('Crea/Modifica Variante') }}</h4>
                </div>
                <div class="modal-body">
                    @include('commons.textfield', ['obj' => $variant, 'name' => 'name', 'label' => _i('Nome'), 'mandatory' => true])

                    <div class="form-group">
                        <label class="col-sm-{{ $labelsize }} control-label">{{ _i('Valori') }}</label>
                        <div class="col-sm-{{ $fieldsize }} values_table">
                            @include('commons.manyrows', [
                                'contents' => $variant ? $variant->values : null,
                                'columns' => [
                                    [
                                        'label' => _i('Valore'),
                                        'field' => 'value',
                                        'type' => 'text'
                                    ],
                                ],
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
