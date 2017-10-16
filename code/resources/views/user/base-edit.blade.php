@include('commons.textfield', ['obj' => $user, 'name' => 'username', 'label' => 'Login', 'mandatory' => true])
@include('commons.textfield', ['obj' => $user, 'name' => 'firstname', 'label' => 'Nome', 'mandatory' => true])
@include('commons.textfield', ['obj' => $user, 'name' => 'lastname', 'label' => 'Cognome', 'mandatory' => true])
@include('commons.passwordfield', ['obj' => $user, 'name' => 'password', 'label' => 'Password', 'mandatory' => true])
