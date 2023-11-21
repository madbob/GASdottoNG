<x-larastrap::mform :obj="$product" classes="product-editor" method="PUT" :action="route('products.update', $product->id)" :other_buttons="[['color' => 'light', 'label' => _i('Duplica'), 'attributes' => ['data-modal-url' => route('products.duplicate', $product->id)], 'classes' => ['float-start', 'async-modal']]]">
    @include('product.editform', ['product' => $product])
    <hr>
</x-larastrap::mform>
