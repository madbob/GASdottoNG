<form class="form-horizontal main-form" method="PUT" action="{{ url('suppliers/' . $supplier->id) }}">
    <input type="hidden" name="id" value="{{ $supplier->id }}" />

    <div class="row">
        <div class="col-md-6">
            @include('commons.staticstringfield', ['obj' => $supplier, 'name' => 'name', 'label' => 'Nome'])
            @include('commons.staticstringfield', ['obj' => $supplier, 'name' => 'business_name', 'label' => 'Ragione Sociale'])
            @include('commons.staticstringfield', ['obj' => $supplier, 'name' => 'description', 'label' => 'Descrizione'])
            @include('commons.staticstringfield', ['obj' => $supplier, 'name' => 'taxcode', 'label' => 'Codice Fiscale'])
            @include('commons.staticstringfield', ['obj' => $supplier, 'name' => 'vat', 'label' => 'Partita IVA'])
            @include('commons.staticcontactswidget', ['obj' => $supplier])
        </div>
        <div class="col-md-6">
            @include('commons.permissionseditor', ['object' => $supplier, 'master_permission' => 'supplier.modify', 'editable' => $editable])
        </div>
    </div>
</form>
