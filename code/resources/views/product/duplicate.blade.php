<x-larastrap::modal :title="_i('Duplica Prodotto')">
    <x-larastrap::form classes="product-editor creating-form" method="POST" :action="route('products.store')">
        <x-larastrap::hidden name="supplier_id" />
        <x-larastrap::hidden name="duplicating_from" :value="$product->id" />
        <input type="hidden" name="update-list" value="product-list-{{ $product->supplier_id }}">
        @include('product.editform', ['product' => $product, 'duplicate' => true])
    </x-larastrap::form>
</x-larastrap::modal>
