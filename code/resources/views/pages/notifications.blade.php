@extends($theme_layout)

@section('content')

<div class="row">
    <div class="col-md-12">
        @can('notifications.admin', $currentgas)
            @include('commons.addingbutton', [
                'template' => 'notification.base-edit',
                'typename' => 'notification',
                'typename_readable' => _i('Notifica'),
                'targeturl' => 'notifications'
            ])
        @endcan
    </div>

    <div class="clearfix"></div>
    <hr/>
</div>

<div class="row">
    <div class="col-md-12">
        @include('commons.loadablelist', ['identifier' => 'notification-list', 'items' => $notifications, 'url' => url('notifications/')])
    </div>
</div>

@endsection
