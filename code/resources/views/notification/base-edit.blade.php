<?php

if(!isset($select_users))
    $select_users = true;

?>

@if($select_users)
    @include('commons.multipleusers', ['obj' => $notification, 'name' => 'users', 'label' => _i('Destinatari')])
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
    'help_text' => $notification && $notification->mailed ? _i('Questa notifica è già stata inoltrata via mail. Salvandola mantenendo questo flag attivo verrà inviata una nuova mail.') : _i('Se abiliti questa opzione la notifica sarà subito inoltrata via mail. Se intendi modificarla prima di inoltrarla, attiva questa opzione solo dopo aver salvato e modificato la notifica.')
])
