<x-larastrap::text name="username" :label="_i('Username')" required :pattern="usernamePattern()" :pophelp="_i('Username col quale l\'utente si può autenticare. Deve essere univoco.')" />
<x-larastrap::text name="firstname" :label="_i('Nome')" required />
<x-larastrap::text name="lastname" :label="_i('Cognome')" required />

@include('commons.passwordfield', [
    'obj' => $user,
    'name' => 'password',
    'label' => _i('Password'),
    'mandatory' => true,
])
