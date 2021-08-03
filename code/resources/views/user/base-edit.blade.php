<x-larastrap::text name="username" :label="_i('Username')" required :pattern="App\User::usernamePattern()" :pophelp="_i('Username col quale l\'utente si può autenticare. Deve essere univoco. Può essere uguale all\'indirizzo email')" />
<x-larastrap::text name="firstname" :label="_i('Nome')" required />
<x-larastrap::text name="lastname" :label="_i('Cognome')" required />

<x-larastrap::check name="sendmail" :label="_i('Invia E-Mail')" switch classes="collapse_trigger" checked />

<div class="collapse show" data-triggerable="sendmail">
    <div class="col">
        <x-larastrap::field>
            <p class="alert alert-info">
                {{ _i("Verrà inviata una email all'utente, con cui potrà accedere la prima volta e definire la propria password.") }}
            </p>
        </x-larastrap::field>

        <x-larastrap::email name="email" :label="_i('E-Mail')" />
    </div>
</div>

<div class="collapse" data-triggerable-reverse="sendmail">
    <div class="col">
        @include('commons.passwordfield', [
            'obj' => $user,
            'name' => 'password',
            'label' => _i('Password'),
            'mandatory' => true,
        ])
    </div>
</div>
