@include('commons.textfield', [
    'obj' => $user,
    'name' => 'username',
    'label' => _i('Username'),
    'mandatory' => true,
    'pattern' => App\User::usernamePattern(),
    'help_popover' => _i("Username col quale l'utente si può autenticare. Deve essere univoco."),
])

@include('commons.textfield', [
    'obj' => $user,
    'name' => 'firstname',
    'label' => _i('Nome'),
    'mandatory' => true,
])

@include('commons.textfield', [
    'obj' => $user,
    'name' => 'lastname',
    'label' => _i('Cognome'),
    'mandatory' => true,
])

@include('commons.passwordfield', [
    'obj' => $user,
    'name' => 'password',
    'label' => _i('Password'),
    'mandatory' => true,
])
