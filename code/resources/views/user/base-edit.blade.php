@include('commons.textfield', ['obj' => $user, 'name' => 'username', 'label' => _i('Username'), 'mandatory' => true, 'pattern' => '[A-Za-z0-9_@.\-]{1,50}'])
@include('commons.textfield', ['obj' => $user, 'name' => 'firstname', 'label' => _i('Nome'), 'mandatory' => true])
@include('commons.textfield', ['obj' => $user, 'name' => 'lastname', 'label' => _i('Cognome'), 'mandatory' => true])
@include('commons.passwordfield', ['obj' => $user, 'name' => 'password', 'label' => _i('Password'), 'mandatory' => true])
