@if(!empty($txt_message))
    <p>
        {!! nl2br($txt_message) !!}
    </p>
@else
    <p>
        {{ __('notifications.notices.attached_order', ['gasname' => $currentuser->gas->printableName]) }}
    </p>
    <p>
        {{ __('notifications.greetings') }},<br>
        {{ $currentuser->printableName() }}
    </p>
@endif
