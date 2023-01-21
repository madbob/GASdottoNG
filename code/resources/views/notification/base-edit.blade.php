<?php

if(!isset($select_users)) {
    $select_users = true;
}

if (!isset($instant)) {
    $instant = false;
}

$content_help = '';
$mailtype_id = null;

if (isset($mailtype) == false) {
	if ($notification) {
		$mailtype = $notification->mailtype;
	}
	else {
		$mailtype = null;
	}
}

if (filled($mailtype)) {
	$meta = systemParameters('MailTypes')[$mailtype];
	$mailtype_id = $mailtype;
	$content_help = $meta->formatParams();
}

?>

@if($mailtype_id)
	<x-larastrap::hidden name="mailtype" :value="$mailtype_id" />
@endif

@if($select_users)
    @include('commons.multipleusers', ['obj' => $notification, 'name' => 'users', 'label' => _i('Destinatari')])
@else
    @if($notification)
        @foreach($notification->users as $user)
            <input type="hidden" name="users[]" value="{{ $user->id }}">
        @endforeach
    @endif
@endif

<x-larastrap::textarea name="content" :label="_i('Contenuto')" :help="$content_help" required />
<x-larastrap::datepicker name="start_date" :label="_i('Inizio')" defaults_now required />
<x-larastrap::datepicker name="end_date" :label="_i('Scadenza')" defaults_now required />

@if($notification && $notification->attachments->isEmpty() == false)
    <x-larastrap::field :label="_i('Allegato')">
        @foreach($notification->attachments as $attachment)
            <a class="btn btn-info" href="{{ $attachment->download_url }}">
                {{ $attachment->name }} <i class="bi-download"></i>
            </a>
        @endforeach
    </x-larastrap::field>
@else
    <x-larastrap::file name="file" :label="_i('Allegato')" />
@endif

<?php

if ($instant == true) {
    $mail_help = '';
}
else {
    if ($notification && $notification->mailed) {
        $mail_help = _i('Questa notifica è già stata inoltrata via mail. Salvandola mantenendo questo flag attivo verrà inviata una nuova mail.');
    }
    else {
        $mail_help = _i('Se abiliti questa opzione la notifica sarà subito inoltrata via mail. Se intendi modificarla prima di inoltrarla, attiva questa opzione solo dopo aver salvato e modificato la notifica.');
    }
}

?>

<x-larastrap::check name="mailed" :label="_i('Invia Mail')" :help="$mail_help" />
