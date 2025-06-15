<p>
    {{ __('texts.notifications.notices.new_notification_from', ['author' => $notification->creator->printableName()]) }}:
</p>

{!! nl2br($notification->content) !!}
