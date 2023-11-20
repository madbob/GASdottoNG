<div class="row">
    <div class="col">
        @include('commons.addingbutton', [
            'user' => null,
            'template' => 'friend.base-edit',
            'typename' => 'friend',
            'typename_readable' => _i('Amico'),
            'targeturl' => 'friends',
            'extra' => [
                'creator_id' => $user->id,
            ]
        ])
    </div>
</div>

<hr>

<div class="row">
    <div class="col">
        @include('commons.loadablelist', [
            'identifier' => 'friend-list',
            'items' => $user->friends,
            'empty_message' => _i('Aggiungi le informazioni relative agli amici per i quali vuoi creare delle sotto-prenotazioni. Ogni singola prenotazione sarà autonoma, ma trattata come una sola in fase di consegna. Ogni amico può anche avere delle proprie credenziali di accesso, per entrare in GASdotto e popolare da sé le proprie prenotazioni.'),
            'url' => 'users'
        ])
    </div>
</div>
