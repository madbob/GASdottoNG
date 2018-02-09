<ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active"><a href="#details-{{ $supplier->id }}" aria-controls="dettagli" role="tab" data-toggle="tab">{{ _i('Dettagli') }}</a></li>
    <li role="presentation"><a href="#orders-{{ $supplier->id }}" aria-controls="ordini" role="tab" data-toggle="tab">{{ _i('Ordini') }}</a></li>
    <li role="presentation"><a href="#products-{{ $supplier->id }}" aria-controls="prodotti" role="tab" data-toggle="tab">{{ _i('Prodotti') }}</a></li>
    <li role="presentation"><a href="#files-{{ $supplier->id }}" aria-controls="files" role="tab" data-toggle="tab">{{ _i('Files') }}</a></li>
</ul>

<div class="tab-content">
    <div role="tabpanel" class="tab-pane active" id="details-{{ $supplier->id }}">
        @include('supplier.base_show', ['supplier' => $supplier, 'editable' => true])
    </div>

    <div role="tabpanel" class="tab-pane fade" id="orders-{{ $supplier->id }}">
        @include('supplier.orders', ['supplier' => $supplier])
    </div>

    <div role="tabpanel" class="tab-pane fade" id="products-{{ $supplier->id }}">
        @include('supplier.products', ['supplier' => $supplier])
    </div>

    <div role="tabpanel" class="tab-pane fade" id="files-{{ $supplier->id }}">
        @include('supplier.files', ['supplier' => $supplier])
    </div>
</div>
