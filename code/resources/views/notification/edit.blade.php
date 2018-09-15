<form class="form-horizontal main-form user-editor" method="PUT" action="{{ route('notifications.update', $notification->id) }}">
    <div class="row">
        <div class="col-md-6">
            @include('notification.base-edit', ['notification' => $notification])
        </div>
        <div class="col-md-6">
            <ul class="list-group">
                @foreach($notification->users as $user)
                    <li class="list-group-item">
                        {{ $user->printableName() }}
                        @if($user->pivot->done)
                            <span class="badge">
                                <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
                            </span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    @include('commons.formbuttons')
</form>

@stack('postponed')
