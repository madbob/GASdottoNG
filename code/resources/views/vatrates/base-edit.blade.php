@include('commons.textfield', ['obj' => $vatrate, 'name' => 'name', 'label' => 'Nome'])
@include('commons.decimalfield', ['obj' => $vatrate, 'name' => 'percentage', 'label' => 'Aliquota', 'decimals' => 2])
