<x-larastrap::text name="name" :label="_i('Nome del nuovo GAS')" required />

<hr>

<x-larastrap::field label="">
    <div class="form-text">
        {{ _i('Ogni GAS ha i suoi utenti, e qui puoi definire le credenziali per il primo utente del nuovo GAS. Gli verrà assegnato il "Ruolo Amministratore Multi-GAS" definito nel pannello delle configurazioni dei permessi.') }}
    </div>
</x-larastrap::field>

<x-larastrap::text name="username" :label="_i('Username')" required />
<x-larastrap::text name="firstname" :label="_i('Nome')" required />
<x-larastrap::text name="lastname" :label="_i('Cognome')" required />
<x-larastrap::password name="password" :label="_i('Password')" required />
