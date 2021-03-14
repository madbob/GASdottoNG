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
