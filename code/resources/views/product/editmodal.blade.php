<x-larastrap::modal>
    <x-larastrap::mform :obj="$product" classes="product-editor" method="PUT" :action="route('products.update', $product->id)" :nodelete="true">
        {{--
            Nel contesto di un ordine, questo è per ricaricare la tabella dei
            prodotti per mostrare i dati aggiornati
        --}}
        <input type="hidden" name="reload-portion" value=".order-summary-wrapper">

        <input type="hidden" name="close-modal" value="1">

        @include('product.editform', ['product' => $product])
    </x-larastrap::mform>
</x-larastrap::modal>
