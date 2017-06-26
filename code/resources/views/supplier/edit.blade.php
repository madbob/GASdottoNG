<ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active"><a href="#details-{{ $supplier->id }}" aria-controls="details-{{ $supplier->id }}" role="tab" data-toggle="tab">Dettagli</a></li>
    <li role="presentation"><a href="#orders-{{ $supplier->id }}" aria-controls="orders-{{ $supplier->id }}" role="tab" data-toggle="tab">Ordini</a></li>
    <li role="presentation"><a href="#products-{{ $supplier->id }}" aria-controls="products-{{ $supplier->id }}" role="tab" data-toggle="tab">Prodotti</a></li>
    <li role="presentation"><a href="#files-{{ $supplier->id }}" aria-controls="files-{{ $supplier->id }}" role="tab" data-toggle="tab">Files</a></li>
    @if(Gate::check('movements.view', $currentgas) || Gate::check('movements.admin', $currentgas))
        <li role="presentation"><a href="#accounting-{{ $supplier->id }}" aria-controls="accounting-{{ $supplier->id }}" role="tab" data-toggle="tab">Contabilità</a></li>
    @endif
</ul>

<div class="tab-content">
    <div role="tabpanel" class="tab-pane active" id="details-{{ $supplier->id }}">
        <form class="form-horizontal main-form supplier-editor" method="PUT" action="{{ url('suppliers/' . $supplier->id) }}">
            <input type="hidden" name="id" value="{{ $supplier->id }}" />

            <div class="row">
                <div class="col-md-6">
                    @include('supplier.base-edit', ['supplier' => $supplier])
                    @include('commons.contactswidget', ['obj' => $supplier])
                </div>
                <div class="col-md-6">
                    @include('commons.permissionseditor', ['object' => $supplier, 'master_permission' => 'supplier.modify'])
                </div>
            </div>

            @include('commons.formbuttons')
        </form>
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

    <div role="tabpanel" class="tab-pane fade" id="accounting-{{ $supplier->id }}">
        @include('supplier.accounting', ['supplier' => $supplier])
    </div>
</div>
