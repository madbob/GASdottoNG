<x-larastrap::text name="username" tlabel="auth.username" required :pattern="usernamePattern()" :pophelp="_i('Username col quale l\'utente si può autenticare. Deve essere univoco. Può essere uguale all\'indirizzo email')" />
<x-larastrap::text name="firstname" tlabel="user.firstname" required />
<x-larastrap::text name="lastname" tlabel="user.lastname" required />

<x-larastrap::check name="sendmail" :label="_i('Invia E-Mail')" checked :attributes="['data-bs-toggle' => 'collapse', 'data-bs-target' => '.alternate_behavior']" />

<x-larastrap::collapse classes="alternate_behavior" open>
    <x-larastrap::suggestion>
        {{ _i("Verrà inviata una email all'utente, con cui potrà accedere la prima volta e definire la propria password.") }}
    </x-larastrap::suggestion>

    <x-larastrap::email name="email" tlabel="generic.email" />
</x-larastrap::collapse>

<x-larastrap::collapse classes="alternate_behavior">
    @include('commons.passwordfield', [
        'obj' => $user,
        'name' => 'password',
        'label' => __('auth.password'),
        'classes' => 'required_when_triggered',
    ])
</x-larastrap::collapse>
