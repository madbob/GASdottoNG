<p>
    {{ _i('Nuovo utente registrato su %s:', $user->gas->name) }}
</p>
<p>
    {{ $user->printableName() }}<br>
    {{ $user->email }}
</p>
