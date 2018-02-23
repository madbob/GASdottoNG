@include('commons.textfield', [
    'obj' => $delivery,
    'name' => 'name',
    'label' => _i('Nome'),
    'mandatory' => true
])

@include('commons.addressfield', [
    'obj' => $delivery,
    'name' => 'address',
    'label' => _i('Indirizzo'),
    'mandatory' => true
])

@include('commons.boolfield', [
    'obj' => $delivery,
    'name' => 'default',
    'label' => _i('Abilitato di Default')
])
