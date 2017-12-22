@include('commons.textfield', ['obj' => $vatrate, 'name' => 'name', 'label' => _i('Nome')])
@include('commons.decimalfield', ['obj' => $vatrate, 'name' => 'percentage', 'label' => _i('Aliquota'), 'decimals' => 2])
