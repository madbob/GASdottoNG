@include('commons.textfield', ['obj' => $attachment, 'name' => 'name', 'label' => _i('Nome')])
@include('commons.filefield', ['obj' => $attachment, 'name' => 'file', 'label' => _i('File'), 'mandatory' => true])
