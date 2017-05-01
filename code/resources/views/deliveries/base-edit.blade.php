@include('commons.textfield', ['obj' => $delivery, 'name' => 'name', 'label' => 'Nome'])
@include('commons.textfield', ['obj' => $delivery, 'name' => 'address', 'label' => 'Indirizzo'])
@include('commons.boolfield', ['obj' => $delivery, 'name' => 'default', 'label' => 'Abilitato di Default'])
