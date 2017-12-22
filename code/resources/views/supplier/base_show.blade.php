<form class="form-horizontal main-form" method="PUT" action="{{ url('suppliers/' . $supplier->id) }}">
    <input type="hidden" name="id" value="{{ $supplier->id }}" />

    <div class="row">
        <div class="col-md-6">
            @include('commons.staticstringfield', ['obj' => $supplier, 'name' => 'name', 'label' => _i('Nome')])
            @include('commons.staticstringfield', ['obj' => $supplier, 'name' => 'business_name', 'label' => _i('Ragione Sociale')])
            @include('commons.staticstringfield', ['obj' => $supplier, 'name' => 'description', 'label' => _i('Descrizione')])
            @include('commons.staticstringfield', ['obj' => $supplier, 'name' => 'taxcode', 'label' => _i('Codice Fiscale')])
            @include('commons.staticstringfield', ['obj' => $supplier, 'name' => 'vat', 'label' => _i('Partita IVA')])
            @include('commons.staticcontactswidget', ['obj' => $supplier])
        </div>
        <div class="col-md-6">
            @include('commons.permissionseditor', ['object' => $supplier, 'master_permission' => 'supplier.modify', 'editable' => $editable])
        </div>
    </div>
</form>
