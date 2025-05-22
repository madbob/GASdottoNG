<x-larastrap::tabs active="0">
    <x-larastrap::tabpane tlabel="generic.details" icon="bi-tags">
        @include('supplier.base_show', ['supplier' => $supplier, 'editable' => true, 'selfview' => false])
    </x-larastrap::tabpane>

    <x-larastrap::tabpane tlabel="orders.all" icon="bi-list-task">
        @include('supplier.orders', ['supplier' => $supplier])
    </x-larastrap::tabpane>

    <x-larastrap::tabpane tlabel="products.list" icon="bi-cart">
        @include('supplier.products', ['supplier' => $supplier])
    </x-larastrap::tabpane>

    <x-larastrap::tabpane tlabel="supplier.attachments" icon="bi-files">
        @include('supplier.files', ['supplier' => $supplier])
    </x-larastrap::tabpane>

    @if(Gate::check('movements.view', $currentgas) || Gate::check('movements.admin', $currentgas))
        <x-larastrap::tabpane tlabel="generic.menu.accounting" icon="bi-piggy-bank">
            @include('supplier.accounting', ['supplier' => $supplier])
        </x-larastrap::tabpane>
    @endif
</x-larastrap::tabs>
