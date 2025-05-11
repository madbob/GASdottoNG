<x-larastrap::text name="name" :label="_i('Nome del nuovo GAS')" required />

<hr>

<x-larastrap::field label="">
    <div class="form-text">
        {{ _i('Ogni GAS ha i suoi utenti, e qui puoi definire le credenziali per il primo utente del nuovo GAS. Gli verrà assegnato il "Ruolo Amministratore Multi-GAS" definito nel pannello delle configurazioni dei permessi.') }}
    </div>
</x-larastrap::field>

<x-larastrap::text name="username" tlabel="auth.username" required />
<x-larastrap::text name="firstname" tlabel="user.firstname" required />
<x-larastrap::text name="lastname" tlabel="user.lastname" required />
<x-larastrap::password name="password" tlabel="auth.password" required />
