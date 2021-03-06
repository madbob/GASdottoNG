<ul class="nav nav-tabs" role="tablist">
    <li role="presentation" class="active"><a href="#details-{{ $supplier->id }}" aria-controls="details-{{ $supplier->id }}" role="tab" data-toggle="tab">{{ _i('Dettagli') }}</a></li>
    <li role="presentation"><a href="#orders-{{ $supplier->id }}" aria-controls="orders-{{ $supplier->id }}" role="tab" data-toggle="tab">{{ _i('Ordini') }}</a></li>
    <li role="presentation"><a href="#products-{{ $supplier->id }}" aria-controls="products-{{ $supplier->id }}" role="tab" data-toggle="tab">{{ _i('Prodotti') }}</a></li>
    <li role="presentation"><a href="#files-{{ $supplier->id }}" aria-controls="files-{{ $supplier->id }}" role="tab" data-toggle="tab">{{ _i('File e Immagini') }}</a></li>
    @if(Gate::check('movements.view', $currentgas) || Gate::check('movements.admin', $currentgas))
        <li role="presentation"><a href="#accounting-{{ $supplier->id }}" aria-controls="accounting-{{ $supplier->id }}" role="tab" data-toggle="tab">{{ _i('Contabilità') }}</a></li>
    @endif
</ul>

<div class="tab-content">
    <div role="tabpanel" class="tab-pane active" id="details-{{ $supplier->id }}">
        <form class="form-horizontal main-form supplier-editor" method="PUT" action="{{ route('suppliers.update', $supplier->id) }}">
            <input type="hidden" name="id" value="{{ $supplier->id }}" />

            <div class="row">
                <div class="col-md-6">
                    @include('supplier.base-edit', ['supplier' => $supplier])
                    @include('commons.contactswidget', ['obj' => $supplier])
                </div>
                <div class="col-md-6">
                    @include('commons.boolfield', [
                        'obj' => $supplier,
                        'name' => 'fast_shipping_enabled',
                        'label' => _i('Abilita Consegne Rapide'),
                        'help_popover' => _i("Quando questa opzione è abilitata, nel pannello degli ordini per questo fornitore viene attivata la tab \"Consegne Rapide\" (accanto a \"Consegne\") che permette di marcare più prenotazioni come consegnate in un'unica operazione"),
                    ])

                    @include('commons.statusfield', ['target' => $supplier])
                    <hr>
                    @include('commons.permissionseditor', ['object' => $supplier, 'master_permission' => 'supplier.modify', 'editable' => true])
                </div>
            </div>

            @include('commons.formbuttons', [
                'obj' => $supplier,
                'no_delete' => ($currentuser->can('supplier.add', $currentgas) && $supplier->orders()->count() > 0),
            ])
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
