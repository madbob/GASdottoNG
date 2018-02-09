@include('commons.textfield', ['obj' => $delivery, 'name' => 'name', 'label' => _i('Nome')])
@include('commons.addressfield', ['obj' => $delivery, 'name' => 'address', 'label' => _i('Indirizzo')])
@include('commons.boolfield', ['obj' => $delivery, 'name' => 'default', 'label' => _i('Abilitato di Default')])
