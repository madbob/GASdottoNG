@include('commons.textfield', ['obj' => null, 'name' => 'name', 'label' => _i('Nome'), 'mandatory' => true])

<hr>
<h4>Nuovo Utente Amministratore</h4>

@include('commons.textfield', ['obj' => null, 'name' => 'username', 'label' => _i('Username'), 'mandatory' => true])
@include('commons.textfield', ['obj' => null, 'name' => 'firstname', 'label' => _i('Nome'), 'mandatory' => true])
@include('commons.textfield', ['obj' => null, 'name' => 'lastname', 'label' => _i('Cognome'), 'mandatory' => true])
@include('commons.passwordfield', ['obj' => null, 'name' => 'password', 'label' => _i('Password'), 'mandatory' => true, 'extra_class' => 'password-changer'])
