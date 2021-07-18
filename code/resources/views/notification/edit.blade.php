<x-larastrap::form :obj="$notification" classes="main-form user-editor" method="PUT" :action="route('notifications.update', $notification->id)">
    <div class="row">
        <div class="col-md-6">
            @include('notification.base-edit', ['notification' => $notification])
        </div>
        <div class="col-md-6 d-none d-md-block">
            <ul class="list-group">
                @foreach($notification->users as $user)
                    <li class="list-group-item">
                        {{ $user->printableName() }}
                        @if($user->pivot->done)
                            <span class="badge">
                                <i class="bi-check"></i>
                            </span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</x-larastrap::form>

@stack('postponed')
