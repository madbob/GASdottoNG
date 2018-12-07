<div class="modal fade" id="duplicate-product-{{ $product->id }}" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-extra-lg" role="document">
        <div class="modal-content">
            <form class="form-horizontal product-editor creating-form" method="POST" action="{{ route('products.store') }}">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{{ _i('Duplica Prodotto') }}</h4>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="supplier_id" value="{{ $product->supplier_id }}">
                    <input type="hidden" name="duplicating_from" value="{{ $product->id }}">
                    <input type="hidden" name="update-list" value="product-list-{{ $product->supplier_id }}">
                    @include('product.editform', ['product' => $product, 'duplicate' => true])
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ _i('Annulla') }}</button>
                    <button type="submit" class="btn btn-success">{{ _i('Salva') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
