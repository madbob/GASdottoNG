@include('commons.selectobjfield', [
    'obj' => $invoice,
    'name' => 'supplier_id',
    'label' => _i('Fornitore'),
    'mandatory' => true,
    'objects' => App\Supplier::orderBy('name', 'asc')->get()
])

@include('commons.textfield', [
    'obj' => $invoice,
    'name' => 'number',
    'label' => _i('Numero'),
    'mandatory' => true
])

@include('commons.datefield', [
    'obj' => $invoice,
    'name' => 'date',
    'label' => _i('Data'),
    'mandatory' => true
])

@include('commons.decimalfield', [
    'obj' => $invoice,
    'name' => 'total',
    'label' => _i('Totale Imponibile'),
    'mandatory' => true
])

@include('commons.decimalfield', [
    'obj' => $invoice,
    'name' => 'total_vat',
    'label' => _i('Totale IVA'),
    'mandatory' => true
])
