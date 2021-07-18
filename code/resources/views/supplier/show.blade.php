<x-larastrap::tabs>
    <x-larastrap::tabpane active="true" :label="_i('Dettagli')">
        @include('supplier.base_show', ['supplier' => $supplier, 'editable' => true])
    </x-larastrap::tabpane>

    <x-larastrap::tabpane :label="_i('Ordini')">
        @include('supplier.orders', ['supplier' => $supplier])
    </x-larastrap::tabpane>

    <x-larastrap::tabpane :label="_i('Prodotti')">
        @include('supplier.products', ['supplier' => $supplier])
    </x-larastrap::tabpane>

    <x-larastrap::tabpane :label="_i('File e Immagini')">
        @include('supplier.files', ['supplier' => $supplier])
    </x-larastrap::tabpane>

    @if(Gate::check('movements.view', $currentgas) || Gate::check('movements.admin', $currentgas))
        <x-larastrap::tabpane :label="_i('Contabilità')">
            @include('supplier.accounting', ['supplier' => $supplier])
        </x-larastrap::tabpane>
    @endif
</x-larastrap::tabs>
