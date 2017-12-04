<p>
    {{ _i('Nuova notifica da parte di %s', $notification->creator->printableName()) }}:
</p>

{!! $notification->content !!}
