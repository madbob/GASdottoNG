<x-larastrap::text name="name" :label="_i('Nome')" required />

<hr>
<h4>Nuovo Utente Amministratore</h4>

<x-larastrap::text name="username" :label="_i('Username')" required />
<x-larastrap::text name="firstname" :label="_i('Nome')" required />
<x-larastrap::text name="lastname" :label="_i('Cognome')" required />
<x-larastrap::password name="password" :label="_i('Password')" required />
