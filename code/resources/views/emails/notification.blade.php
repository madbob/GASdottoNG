<p>
    {{ _i('Nuova notifica da parte di %s', $notification->creator->printableName()) }}:
</p>

{!! nl2br($notification->content) !!}
