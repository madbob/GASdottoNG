@php

if(!isset($select_users)) {
    $select_users = true;
}

if (!isset($instant)) {
    $instant = false;
}

$content_help = '';
$mailtype_id = null;

if (!isset($mailtype)) {
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

@endphp

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

@include('notification.partials.attachment', ['notification' => $notification])

@php

if ($instant) {
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

@endphp

<x-larastrap::check name="mailed" tlabel="generic.send_mail" :help="$mail_help" />
