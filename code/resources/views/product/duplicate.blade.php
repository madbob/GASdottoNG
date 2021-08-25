<x-larastrap::modal :title="_i('Duplica Prodotto')">
    <x-larastrap::mform :obj="$product" classes="product-editor" method="POST" :action="route('products.store')" nodelete="true">
        <input type="hidden" name="test-feedback" value="1">
        <input type="hidden" name="update-list" value="product-list-{{ $product->supplier_id }}">
        <input type="hidden" name="close-modal" value="1">

        <x-larastrap::hidden name="supplier_id" />
        <x-larastrap::hidden name="duplicating_from" :value="$product->id" />

        @include('product.editform', ['product' => $product, 'duplicate' => true])
    </x-larastrap::mform>
</x-larastrap::modal>
