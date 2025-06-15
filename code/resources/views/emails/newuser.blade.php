<p>
    {{ __('texts.user.notices.new_user', ['gasname' => $user->gas->name]) }}
</p>
<p>
    {{ $user->printableName() }}<br>
    {{ $user->email }}<br>

    @foreach($user->getContactsByType(['phone', 'mobile']) as $phone)
        {{ $phone }}<br>
    @endforeach
</p>

@if($user->pending)
    <p>
        {{ __('texts.user.notices.pending_approval') }}<br>
        {{ route('users.index') }}
    </p>
@endif
