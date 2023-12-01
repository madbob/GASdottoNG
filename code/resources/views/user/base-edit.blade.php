<x-larastrap::text name="username" :label="_i('Username')" required :pattern="usernamePattern()" :pophelp="_i('Username col quale l\'utente si può autenticare. Deve essere univoco. Può essere uguale all\'indirizzo email')" />
<x-larastrap::text name="firstname" :label="_i('Nome')" required />
<x-larastrap::text name="lastname" :label="_i('Cognome')" required />

<x-larastrap::check name="sendmail" :label="_i('Invia E-Mail')" checked :attributes="['data-bs-toggle' => 'collapse', 'data-bs-target' => '.alternate_behavior']" />

<x-larastrap::collapse classes="alternate_behavior" open>
    <x-larastrap::suggestion>
        {{ _i("Verrà inviata una email all'utente, con cui potrà accedere la prima volta e definire la propria password.") }}
    </x-larastrap::suggestion>

    <x-larastrap::email name="email" :label="_i('E-Mail')" />
</x-larastrap::collapse>

<x-larastrap::collapse classes="alternate_behavior">
    @include('commons.passwordfield', [
        'obj' => $user,
        'name' => 'password',
        'label' => _i('Password'),
        'classes' => 'required_when_triggered',
    ])
</x-larastrap::collapse>
