<x-larastrap::tabs active="0">
    <x-larastrap::tabpane :label="_i('Dettagli')" icon="bi-tags">
        @include('supplier.base_show', ['supplier' => $supplier, 'editable' => true, 'selfview' => false])
    </x-larastrap::tabpane>

    <x-larastrap::tabpane :label="_i('Ordini')" icon="bi-list-task">
        @include('supplier.orders', ['supplier' => $supplier])
    </x-larastrap::tabpane>

    <x-larastrap::tabpane tlabel="products.list" icon="bi-cart">
        @include('supplier.products', ['supplier' => $supplier])
    </x-larastrap::tabpane>

    <x-larastrap::tabpane :label="_i('File e Immagini')" icon="bi-files">
        @include('supplier.files', ['supplier' => $supplier])
    </x-larastrap::tabpane>

    @if(Gate::check('movements.view', $currentgas) || Gate::check('movements.admin', $currentgas))
        <x-larastrap::tabpane :label="_i('Contabilità')" icon="bi-piggy-bank">
            @include('supplier.accounting', ['supplier' => $supplier])
        </x-larastrap::tabpane>
    @endif
</x-larastrap::tabs>
