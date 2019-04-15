@include('commons.textfield', ['obj' => $attachment, 'name' => 'name', 'label' => _i('Nome')])
@include('commons.filefield', ['obj' => $attachment, 'name' => 'file', 'label' => _i('File'), 'mandatory' => true])
@include('commons.multipleusers', ['obj' => $attachment, 'name' => 'users', 'label' => _i('Destinatari')])
