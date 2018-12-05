<?php

if(!isset($select_users))
    $select_users = true;

?>

@if($select_users)
    <?php

    foreach(App\Role::orderBy('name', 'asc')->get() as $role) {
        $extras['special::role::' . $role->id] = _i('Tutti gli utenti con ruolo "%s"', [$role->name]);
    }

    foreach ($currentgas->aggregates as $aggregate) {
        foreach($aggregate->orders()->where('status', '!=', 'closed')->where('status', '!=', 'archived')->get() as $order)
            $extras['special::order::'.$order->id] = _i("Tutti i Partecipanti all'ordine %s %s", $order->supplier->name, $order->internal_number);
    }

    ?>

    @include('commons.selectobjfield', [
        'obj' => $notification,
        'name' => 'users',
        'objects' => $currentgas->users,
        'extra_selection' => $extras,
        'multiple_select' => true,
        'label' => _i('Destinatari'),
        'help_text' => _i('Tenere premuto Ctrl per selezionare più utenti. Se nessun utente viene selezionato, la notifica sarà destinata a tutti.')
    ])
@else
    @if($notification)
        @foreach($notification->users as $user)
            <input type="hidden" name="users[]" value="{{ $user->id }}">
        @endforeach
    @endif
@endif

@include('commons.textarea', ['obj' => $notification, 'name' => 'content', 'label' => _i('Contenuto'), 'mandatory' => true])
@include('commons.datefield', ['obj' => $notification, 'name' => 'start_date', 'label' => _i('Inizio'), 'defaults_now' => true, 'mandatory' => true])
@include('commons.datefield', ['obj' => $notification, 'name' => 'end_date', 'label' => _i('Scadenza'), 'defaults_now' => true, 'mandatory' => true])

@include('commons.boolfield', [
    'obj' => $notification,
    'name' => 'mailed',
    'label' => _i('Invia Mail'),
    'help_text' => $notification && $notification->mailed ? _i('Questa notifica è già stata inoltrata via mail. Salvandola mantenendo questo flag attivo verrà inviata una nuova mail.') : ''
])
