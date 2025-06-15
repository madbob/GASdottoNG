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
    @include('commons.multipleusers', ['obj' => $notification, 'name' => 'users', 'label' => __('texts.generic.recipients')])
@else
    @if($notification)
        @foreach($notification->users as $user)
            <input type="hidden" name="users[]" value="{{ $user->id }}">
        @endforeach
    @endif
@endif

<x-larastrap::textarea name="content" tlabel="generic.mailfield.body" :help="$content_help" required />
<x-larastrap::datepicker name="start_date" tlabel="generic.start" defaults_now required />
<x-larastrap::datepicker name="end_date" tlabel="generic.expiration" defaults_now required />

@if($notification && $notification->attachments->isEmpty() == false)
    <x-larastrap::field tlabel="generic.attachment">
        @foreach($notification->attachments as $attachment)
            <a class="btn btn-info" href="{{ $attachment->download_url }}">
                {{ $attachment->name }} <i class="bi-download"></i>
            </a>
        @endforeach
    </x-larastrap::field>
@else
    <x-larastrap::file name="file" tlabel="generic.attachment" />
@endif

<?php

if ($instant == true) {
    $mail_help = '';
}
else {
    if ($notification && $notification->mailed) {
        $mail_help = __('texts.notifications.help.repeat_mail_warning');
    }
    else {
        $mail_help = __('texts.notifications.help.sending_mail_warning');
    }
}

?>

<x-larastrap::check name="mailed" tlabel="generic.send_mail" :help="$mail_help" />
